<?php

/**
 * Title: iDEAL Advanced v3+
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
	 * @param Pronamic_WordPress_IDeal_Configuration $configuration
	 */
	public function __construct( Pronamic_WordPress_IDeal_Configuration $configuration ) {
		parent::__construct();

		$this->set_method( Pronamic_Gateways_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_require_issue_select( true );
		$this->set_amount_minimum( 0.01 );

		// Client
		$client = new Pronamic_Gateways_IDealAdvancedV3_Client();
		$client->set_acquirer_url( $configuration->getPaymentServerUrl() );
		$client->merchant_id          = $configuration->getMerchantId();
		$client->sub_id               = $configuration->getSubId();
		$client->private_key          = $configuration->privateKey;
		$client->private_key_password = $configuration->privateKeyPassword;
		$client->private_certificate  = $configuration->privateCertificate;
		
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
					$issuers[$issuer->get_id()] = $issuer->get_name();
				}
	
				$groups[] = array(
					'name'    => $country->get_name(),
					'options' => $issuers
				);
			}
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
			'choices'  => $this->get_issuers()
		);
	}
	
	/////////////////////////////////////////////////

	public function get_input_fields() {
		$fields = array();
		
		$fields[] = $this->get_issuer_field(); 

		return $fields;
	}
	
	/////////////////////////////////////////////////

	/**
	 * Start
	 * 
	 * @see Pronamic_Gateways_Gateway::start()
	 */
	public function start( Pronamic_IDeal_IDealDataProxy $data ) {
		$transaction = new Pronamic_Gateways_IDealAdvancedV3_Transaction();
		$transaction->set_purchase_id( $data->getOrderId() );
		$transaction->setAmount( $data->getAmount() );
		$transaction->setCurrency( $data->getCurrencyAlphabeticCode() );
		$transaction->setExpirationPeriod( 'PT3M30S' );
		$transaction->setLanguage( $data->getLanguageIso639Code() );
		$transaction->setDescription( $data->getDescription() );
		$transaction->setEntranceCode( $data->get_entrance_code() );

		$result = $this->client->create_transaction( $transaction, $data->get_issuer_id() );

		$error = $this->client->get_error();
		
		if ( $error !== null ) {
			var_dump( $error );
		} else {
			$issuer = $result->issuer;

			$this->action_url     = $result->issuer->get_authentication_url();
			$this->transaction_id = $result->transaction->get_id();
		}
	}
	
	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 * 
	 * @param Pronamic_WordPress_IDeal_Payment $payment
	 */
	public function update_status( Pronamic_WordPress_IDeal_Payment $payment ) {
		$result = $this->client->get_status( $payment->transaction_id );

		$error = $this->client->get_error();

		if ( $error !== null ) {
			var_dump( $error );
		} else {
			$transaction = $result->transaction;

			$payment->status        = $transaction->get_status();
			$payment->consumer_name = $transaction->get_consumer_name();
			$payment->consumer_iban = $transaction->get_consumer_iban();
			$payment->consumer_bic  = $transaction->get_consumer_bic();
		}
	}
}
