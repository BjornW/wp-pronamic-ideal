<?php

/**
 * Title: iDEAL Advanced v3+ gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Gateways_IDealAdvancedV3_Gateway extends Pronamic_Gateways_Gateway {
	/**
	 * Constructs and initializes an iDEAL Advanced v3 gateway
	 *
	 * @param Pronamic_Gateways_IDealAdvancedV3_Config $config
	 */
	public function __construct( Pronamic_Gateways_IDealAdvancedV3_Config $config ) {
		parent::__construct( $config );

		$this->set_method( Pronamic_Gateways_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 0.01 );

		// Client
		$client = new Pronamic_Gateways_IDealAdvancedV3_Client();
		$client->set_acquirer_url( $config->url );
		$client->merchant_id          = $config->merchant_id;
		$client->sub_id               = $config->sub_id;
		$client->private_key          = $config->private_key;
		$client->private_key_password = $config->private_key_password;
		$client->private_certificate  = $config->private_certificate;

		$this->client = $client;
	}

	/////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @see Pronamic_Gateways_Gateway::get_issuers()
	 * @return array
	 */
	public function get_issuers() {
		$groups = array();

		$directory = $this->client->get_directory();

		if ( $directory ) {
			foreach ( $directory->get_countries() as $country ) {
				$issuers = array();

				foreach ( $country->get_issuers() as $issuer ) {
					$issuers[ $issuer->get_id() ] = $issuer->get_name();
				}

				$groups[] = array(
					'name'    => $country->get_name(),
					'options' => $issuers,
				);
			}
		} else {
			$this->error = $this->client->get_error();
		}

		return $groups;
	}

	/////////////////////////////////////////////////

	public function get_issuer_field() {
		return array(
			'id'       => 'pronamic_ideal_issuer_id',
			'name'     => 'pronamic_ideal_issuer_id',
			'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
			'required' => true,
			'type'     => 'select',
			'choices'  => $this->get_transient_issuers(),
		);
	}

	/////////////////////////////////////////////////

	/**
	 * Start
	 *
	 * @see Pronamic_Gateways_Gateway::start()
	 */
	public function start( Pronamic_Pay_PaymentDataInterface $data, Pronamic_Pay_Payment $payment ) {
		$transaction = new Pronamic_Gateways_IDealAdvancedV3_Transaction();
		$transaction->set_purchase_id( $data->get_order_id() );
		$transaction->set_amount( $data->get_amount() );
		$transaction->set_currency( $data->get_currency() );
		$transaction->set_expiration_period( 'PT3M30S' );
		$transaction->set_language( $data->get_language() );
		$transaction->set_description( $data->get_description() );
		$transaction->set_entrance_code( $data->get_entrance_code() );

		$return_url = add_query_arg( 'payment', $payment->get_id(), home_url( '/' ) );

		$result = $this->client->create_transaction( $transaction, $return_url, $data->get_issuer_id() );

		$error = $this->client->get_error();

		if ( is_wp_error( $error ) ) {
			$this->set_error( $error );
		} else {
			$issuer = $result->issuer;

			$payment->set_action_url( $result->issuer->get_authentication_url() );
			$payment->set_transaction_id( $result->transaction->get_id() );
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function update_status( Pronamic_Pay_Payment $payment ) {
		$result = $this->client->get_status( $payment->get_transaction_id() );

		$error = $this->client->get_error();

		if ( is_wp_error( $error ) ) {
			$this->set_error( $error );
		} else {
			$transaction = $result->transaction;

			$payment->set_status( $transaction->get_status() );
			$payment->set_consumer_name( $transaction->get_consumer_name() );
			$payment->set_consumer_iban( $transaction->get_consumer_iban() );
			$payment->set_consumer_bic( $transaction->get_consumer_bic() );
		}
	}

	/////////////////////////////////////////////////

	public function payment( Pronamic_Pay_Payment $payment ) {
		/*
		 * Schedule status requests
		 * http://pronamic.nl/wp-content/uploads/2011/12/iDEAL_Advanced_PHP_EN_V2.2.pdf (page 19)
		 *
		 * @todo
		 * Considering the number of status requests per transaction:
		 * - Maximum of five times per transaction;
		 * - Maximum of two times during the expirationPeriod;
		 * - After the expirationPeriod not more often than once per 60 minutes;
		 * - No status request after a final status has been received for a transaction;
		 * - No status request for transactions older than 7 days.
		 */

		/*
		 * The function wp_schedule_single_event() uses the arguments array as an key for the event,
		 * that's why we also add the time to this array, besides that it's also much clearer on
		 * the Cron View (http://wordpress.org/extend/plugins/cron-view/) page
		 */

		$time = time();

		// Examples of possible times when a status request can be executed:

		// 30 seconds after a transaction request is sent
		wp_schedule_single_event( $time + 30, 'pronamic_ideal_check_transaction_status', array( 'payment_id' => $payment->get_id(), 'seconds' => 30 ) );
		// Half-way through an expirationPeriod
		wp_schedule_single_event( $time + 1800, 'pronamic_ideal_check_transaction_status', array( 'payment_id' => $payment->get_id(), 'seconds' => 1800 ) );
		// Just after an expirationPeriod
		wp_schedule_single_event( $time + 3600, 'pronamic_ideal_check_transaction_status', array( 'payment_id' => $payment->get_id(), 'seconds' => 3600 ) );
		// A certain period after the end of the expirationPeriod
		wp_schedule_single_event( $time + 86400, 'pronamic_ideal_check_transaction_status', array( 'payment_id' => $payment->get_id(), 'seconds' => 86400 ) );
	}
}
