<?php

/**
 * Title: WordPress iDEAL plugin
 * Description:
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_WP_Pay_Plugin {
	/**
	 * The maximum number of payments that can be done without an license
	 *
	 * @var int
	 */
	const PAYMENTS_MAX_LICENSE_FREE = 20;

	//////////////////////////////////////////////////

	/**
	 * The root file of this WordPress plugin
	 *
	 * @var string
	 */
	public static $file;

	/**
	 * The plugin dirname
	 *
	 * @var string
	 */
	public static $dirname;

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 *
	 * @param string $file
	 */
	public static function bootstrap( $file ) {
		self::$file	= $file;
		self::$dirname = dirname( $file );

		// Bootstrap the add-ons
		if ( self::can_be_used() ) {
			Pronamic_WooCommerce_IDeal_AddOn::bootstrap();
			Pronamic_GravityForms_IDeal_AddOn::bootstrap();
			Pronamic_Shopp_IDeal_AddOn::bootstrap();
			Pronamic_Jigoshop_IDeal_AddOn::bootstrap();
			Pronamic_WPeCommerce_IDeal_AddOn::bootstrap();
			Pronamic_ClassiPress_IDeal_AddOn::bootstrap();
			Pronamic_EventEspresso_IDeal_AddOn::bootstrap();
			Pronamic_AppThemes_IDeal_AddOn::bootstrap();
			Pronamic_S2Member_IDeal_AddOn::bootstrap();
			Pronamic_WPMUDEV_Membership_IDeal_AddOn::bootstrap();
			// Pronamic_EShop_IDeal_AddOn::bootstrap();
			Pronamic_EasyDigitalDownloads_IDeal_AddOn::bootstrap();
			Pronamic_IThemesExchange_IDeal_AddOn::bootstrap();
		}

		// Admin
		if ( is_admin() ) {
			Pronamic_WP_Pay_Admin::bootstrap();
		} else {
			add_filter( 'comments_clauses', array( __CLASS__, 'exclude_comment_payment_notes' ) );
		}

		add_action( 'plugins_loaded', array( __CLASS__, 'setup' ) );

		// Initialize requirements
		require_once self::$dirname . '/includes/version.php';
		require_once self::$dirname . '/includes/functions.php';
		require_once self::$dirname . '/includes/formatting.php';
		require_once self::$dirname . '/includes/page-functions.php';
		require_once self::$dirname . '/includes/gravityforms.php';
		require_once self::$dirname . '/includes/providers.php';
		require_once self::$dirname . '/includes/gateways.php';
		require_once self::$dirname . '/includes/payment.php';
		require_once self::$dirname . '/includes/post.php';
		require_once self::$dirname . '/includes/xmlseclibs/xmlseclibs-ing.php';
		require_once self::$dirname . '/includes/wp-e-commerce.php';

		// If WordPress is loaded check on returns and maybe redirect requests
		add_action( 'wp_loaded', array( __CLASS__, 'handle_returns' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'maybe_redirect' ) );

		// The 'pronamic_ideal_check_transaction_status' hook is scheduled the status requests
		add_action( 'pronamic_ideal_check_transaction_status', array( __CLASS__, 'checkStatus' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Comments clauses
	 */
	public static function exclude_comment_payment_notes( $clauses ) {
		$clauses['where'] .= " AND comment_type != 'payment_note'";

		return $clauses;
	}

	//////////////////////////////////////////////////

	/**
	 * Check status of the specified payment
	 *
	 * @param string $paymentId
	 */
	public static function checkStatus( $payment_id = null ) {
		$payment = new Pronamic_WP_Pay_Payment( $payment_id );

		if ( $payment !== null ) {
			// http://pronamic.nl/wp-content/uploads/2011/12/iDEAL_Advanced_PHP_EN_V2.2.pdf (page 19)
			// - No status request after a final status has been received for a transaction;
			$status = $payment->status;

			if ( empty( $status ) || $status === Pronamic_Gateways_IDealAdvancedV3_Status::OPEN ) {
				self::update_payment( $payment );
			}
		} else {
			// Payment with the specified ID could not be found, can't check the status
		}
	}

	public static function update_payment( $payment = null, $can_redirect = true ) {
		if ( $payment ) {
			$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $payment->config_id );

			if ( $gateway ) {
				$old_status = strtolower( $payment->status );

				if ( strlen( $old_status ) <= 0 ) {
					$old_status = 'unknown';
				}

				$gateway->update_status( $payment );

				$new_status = strtolower( $payment->status );

				pronamic_wp_pay_update_payment( $payment );

				do_action( "pronamic_payment_status_update_{$payment->source}_{$old_status}_to_{$new_status}", $payment, $can_redirect );
				do_action( "pronamic_payment_status_update_{$payment->source}", $payment, $can_redirect );
				do_action( 'pronamic_payment_status_update', $payment, $can_redirect );
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Handle returns
	 */
	public static function handle_returns() {
		if ( filter_has_var( INPUT_GET, 'payment' ) ) {
			$payment_id = filter_input( INPUT_GET, 'payment', FILTER_SANITIZE_NUMBER_INT );

			$payment = get_pronamic_payment( $payment_id );

			// Check if we should redirect
			$should_redirect = true;

			// Check if the request is an callback request
			// Sisow gatway will extend callback requests with querystring "callback=true"
			if ( filter_has_var( INPUT_GET, 'callback' ) ) {
				$is_callback = filter_input( INPUT_GET, 'callback', FILTER_VALIDATE_BOOLEAN );

				if ( $is_callback ) {
					$should_redirect = false;
				}
			}

			// Check if the request is an notify request
			// Sisow gatway will extend callback requests with querystring "notify=true"
			if ( filter_has_var( INPUT_GET, 'notify' ) ) {
				$is_notify = filter_input( INPUT_GET, 'notify', FILTER_VALIDATE_BOOLEAN );

				if ( $is_notify ) {
					$should_redirect = false;
				}
			}

			self::update_payment( $payment, $should_redirect );
		}

		Pronamic_Gateways_IDealBasic_Listener::listen();
		Pronamic_Gateways_OmniKassa_Listener::listen();
		Pronamic_Gateways_Icepay_Listener::listen();
		Pronamic_Gateways_Mollie_Listener::listen();
	}

	/**
	 * Maybe redirect
	 */
	public static function maybe_redirect() {
		if ( filter_has_var( INPUT_GET, 'payment_redirect' ) ) {
			$payment_id = filter_input( INPUT_GET, 'payment_redirect', FILTER_SANITIZE_NUMBER_INT );

			$payment = get_pronamic_payment( $payment_id );

			// HTML Answer
			$html_answer = $payment->get_meta( 'ogone_directlink_html_answer' );

			if ( ! empty( $html_answer ) ) {
				echo $html_answer;

				exit;
			}

			// Action URL
			if ( ! empty( $payment->action_url ) ) {
				wp_redirect( $payment->action_url );

				exit;
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Get the key
	 *
	 * @return string
	 */
	public static function get_key() {
		return get_option( 'pronamic_pay_license_key' );
	}

	/**
	 * Get the license info for the current installation on the blogin
	 *
	 * @return stdClass an onbject with license information or null
	 */
	public static function get_license_info() {
		return null;
	}

	/**
	 * Get number payments
	 *
	 * @return int
	 */
	public static function get_number_payments() {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT( ID ) FROM $wpdb->posts WHERE post_type = 'pronamic_payment' AND post_status = 'publish';" );

		return $count;
	}

	/**
	 * Check if there is an valid license key
	 *
	 * @return boolean
	 */
	public static function has_valid_key() {
		$result = strlen( self::get_key() ) == 32;

		$license_info = self::get_license_info();

		if ( $license_info != null && isset( $license_info->isValid ) ) {
			$result = $license_info->isValid;
		}

		return $result;
	}

	/**
	 * Checks if the plugin is installed
	 */
	public static function is_installed() {
		return get_option( 'pronamic_pay_version', false ) !== false;
	}

	/**
	 * Check if the plugin can be used
	 *
	 * @return boolean true if plugin can be used, false otherwise
	 */
	public static function can_be_used() {
		return self::is_installed() && ( self::has_valid_key() || self::get_number_payments() <= self::PAYMENTS_MAX_LICENSE_FREE );
	}

	//////////////////////////////////////////////////

	/**
	 * Configure the specified roles
	 *
	 * @param array $roles
	 */
	public static function set_roles( $roles ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		foreach ( $roles as $role => $data ) {
			if ( isset( $data['display_name'], $data['capabilities'] ) ) {
				$display_name = $data['display_name'];
				$capabilities = $data['capabilities'];

				if ( $wp_roles->is_role( $role ) ) {
					foreach ( $capabilities as $cap => $grant ) {
						$wp_roles->add_cap( $role, $cap, $grant );
					}
				} else {
					$wp_roles->add_role( $role, $display_name, $capabilities );
				}
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Setup, creates or updates database tables. Will only run when version changes
	 */
	public static function setup() {
		// Load plugin text domain
		$rel_path = dirname( plugin_basename( self::$file ) ) . '/languages/';

		load_plugin_textdomain( 'pronamic_ideal', false, $rel_path );

		global $pronamic_pay_version;

		if ( get_option( 'pronamic_pay_version' ) != $pronamic_pay_version ) {
			// Add some new capabilities
			$capabilities = array(
				'read'                           => true,
				'pronamic_ideal'                 => true,
				'pronamic_ideal_configurations'  => true,
				'pronamic_ideal_payments'        => true,
				'pronamic_ideal_settings'        => true,
				'pronamic_ideal_pages_generator' => true,
				'pronamic_ideal_status'          => true,
				'pronamic_ideal_variants'        => true,
				'pronamic_ideal_documentation'   => true,
				'pronamic_ideal_branding'        => true,
			);

			$roles = array(
				'pronamic_ideal_administrator' => array(
					'display_name' => __( 'iDEAL Administrator', 'pronamic_ideal' ),
					'capabilities' => $capabilities,
				) ,
				'administrator' => array(
					'display_name' => __( 'Administrator', 'pronamic_ideal' ),
					'capabilities' => $capabilities,
				)
			);

			self::set_roles( $roles );

			// Update version
			update_option( 'pronamic_pay_version', $pronamic_pay_version );
		}
	}

	//////////////////////////////////////////////////
	// Functions from Pronamic_WordPress_IDeal_IDeal
	//////////////////////////////////////////////////

	/**
	 * Get the translaction of the specified status notifier
	 *
	 * @param string $status
	 * @return string
	 */
	public static function translate_status( $status ) {
		switch ( $status ) {
			case Pronamic_Pay_Gateways_IDeal_Statuses::CANCELLED :
				return __( 'Cancelled', 'pronamic_ideal' );
			case Pronamic_Pay_Gateways_IDeal_Statuses::EXPIRED :
				return __( 'Expired', 'pronamic_ideal' );
			case Pronamic_Pay_Gateways_IDeal_Statuses::FAILURE :
				return __( 'Failure', 'pronamic_ideal' );
			case Pronamic_Pay_Gateways_IDeal_Statuses::OPEN :
				return __( 'Open', 'pronamic_ideal' );
			case Pronamic_Pay_Gateways_IDeal_Statuses::SUCCESS :
				return __( 'Success', 'pronamic_ideal' );
			default:
				return __( 'Unknown', 'pronamic_ideal' );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Get default error message
	 *
	 * @return string
	 */
	public static function get_default_error_message() {
		return __( 'Paying with iDEAL is not possible. Please try again later or pay another way.', 'pronamic_ideal' );
	}

	//////////////////////////////////////////////////

	/**
	 * Get config select options
	 *
	 * @return array
	 */
	public static function get_config_select_options() {
		$gateways = get_posts( array(
				'post_type' => 'pronamic_gateway',
				'nopaging'  => true,
		) );

		$options = array( __( '&mdash; Select Configuration &mdash;', 'pronamic_ideal' ) );

		foreach ( $gateways as $gateway ) {
			$options[ $gateway->ID ] = sprintf(
				'%s (%s)',
				get_the_title( $gateway->ID ),
				get_post_meta( $gateway->ID, '_pronamic_gateway_mode', true )
			);
		}

		return $options;
	}

	//////////////////////////////////////////////////

	/**
	 * Render errors
	 *
	 * @param array $errors
	 */
	public static function render_errors( $errors = array() ) {
		if ( ! is_array( $errors ) ) {
			$errors = array( $errors );
		}

		foreach ( $errors as $error ) {
			include Pronamic_WP_Pay_Plugin::$dirname . '/views/error.php';
		}
	}

	//////////////////////////////////////////////////

	public static function get_gateway( $config_id ) {
		$config = get_pronamic_pay_gateway_config( $config_id );

		$gateway_id = $config->gateway_id;

		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'buckaroo', 'Pronamic_WP_Pay_Gateways_Buckaroo_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'icepay', 'Pronamic_WP_Pay_Gateways_Icepay_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ideal_advanced', 'Pronamic_WP_Pay_Gateways_IDealAdvanced_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ideal_advanced_v3', 'Pronamic_WP_Pay_Gateways_IDealAdvancedV3_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ideal_basic', 'Pronamic_WP_Pay_Gateways_IDealBasic_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'mollie', 'Pronamic_WP_Pay_Gateways_Mollie_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'mollie_ideal', 'Pronamic_WP_Pay_Gateways_Mollie_IDeal_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'multisafepay_connect', 'Pronamic_WP_Pay_Gateways_MultiSafepay_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ogone_directlink', 'Pronamic_WP_Pay_Gateways_Ogone_DirectLink_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ogone_orderstandard', 'Pronamic_WP_Pay_Gateways_Ogone_OrderStandard_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'ogone_orderstandard_easy', 'Pronamic_WP_Pay_Gateways_Ogone_OrderStandardEasy_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'omnikassa', 'Pronamic_WP_Pay_Gateways_OmniKassa_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'paydutch', 'Pronamic_WP_Pay_Gateways_PayDutch_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'qantani', 'Pronamic_WP_Pay_Gateways_Qantani_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'sisow', 'Pronamic_WP_Pay_Gateways_Sisow_ConfigFactory' );
		Pronamic_WP_Pay_Gateways_ConfigProvider::register( 'targetpay', 'Pronamic_WP_Pay_Gateways_TargetPay_ConfigFactory' );

		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Buckaroo_Config', 'Pronamic_Gateways_Buckaroo_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Icepay_Config', 'Pronamic_Gateways_Icepay_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_IDealAdvanced_Config', 'Pronamic_Gateways_IDealAdvanced_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_IDealAdvancedV3_Config', 'Pronamic_Gateways_IDealAdvancedV3_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Pay_Gateways_IDealBasic_Config', 'Pronamic_Gateways_IDealBasic_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Pay_Gateways_Ogone_DirectLink_Config', 'Pronamic_Pay_Gateways_Ogone_DirectLink_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Pay_Gateways_Ogone_OrderStandard_Config', 'Pronamic_Pay_Gateways_Ogone_OrderStandard_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Pay_Gateways_Ogone_OrderStandardEasy_Config', 'Pronamic_Pay_Gateways_Ogone_OrderStandardEasy_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Mollie_Config', 'Pronamic_Gateways_Mollie_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Mollie_IDeal_Config', 'Pronamic_Gateways_Mollie_IDeal_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Pay_Gateways_MultiSafepay_Config', 'Pronamic_Pay_Gateways_MultiSafepay_Connect_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_OmniKassa_Config', 'Pronamic_Gateways_OmniKassa_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_PayDutch_Config', 'Pronamic_Gateways_PayDutch_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Qantani_Config', 'Pronamic_Gateways_Qantani_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_Sisow_Config', 'Pronamic_Gateways_Sisow_Gateway' );
		Pronamic_Pay_GatewayFactory::register( 'Pronamic_Gateways_TargetPay_Config', 'Pronamic_Gateways_TargetPay_Gateway' );

		global $pronamic_pay_gateways;

		if ( isset( $pronamic_pay_gateways[ $gateway_id ] ) ) {
			$gateway      = $pronamic_pay_gateways[ $gateway_id ];
			$gateway_slug = $gateway['gateway'];

			$config = Pronamic_WP_Pay_Gateways_ConfigProvider::get_config( $gateway_slug, $config_id );

			$gateway = Pronamic_Pay_GatewayFactory::create( $config );

			return $gateway;
		}
	}

	public static function start( $config_id, Pronamic_Gateways_Gateway $gateway, Pronamic_Pay_PaymentDataInterface $data ) {
		$payment = self::create_payment( $config_id, $gateway, $data );

		if ( $payment ) {
			$gateway->start( $data, $payment );

			pronamic_wp_pay_update_payment( $payment );

			$gateway->payment( $payment );
		}

		return $payment;
	}

	public static function create_payment( $config_id, $gateway, $data ) {
		$payment = null;

		$result = wp_insert_post( array(
			'post_type'   => 'pronamic_payment',
			'post_title'  => sprintf( __( 'Payment for %s', 'pronamic_ideal' ), $data->get_title() ),
			'post_status' => 'publish',
		), true );

		if ( is_wp_error( $result ) ) {
			// @todo what todo?
		} else {
			$post_id = $result;

			// @todo temporary solution for WPMU DEV
			$data->payment_post_id = $post_id;

			// Meta
			$prefix = '_pronamic_payment_';

			$meta = array(
				$prefix . 'config_id'               => $config_id,
				$prefix . 'purchase_id'             => $data->get_order_id(),
				$prefix . 'currency'                => $data->get_currency(),
				$prefix . 'amount'                  => $data->get_amount(),
				$prefix . 'expiration_period'       => null,
				$prefix . 'language'                => $data->get_language(),
				$prefix . 'entrance_code'           => $data->get_entrance_code(),
				$prefix . 'description'             => $data->get_description(),
				$prefix . 'consumer_name'           => null,
				$prefix . 'consumer_account_number' => null,
				$prefix . 'consumer_iban'           => null,
				$prefix . 'consumer_bic'            => null,
				$prefix . 'consumer_city'           => null,
				$prefix . 'status'                  => null,
				$prefix . 'source'                  => $data->get_source(),
				$prefix . 'source_id'               => $data->get_source_id(),
				$prefix . 'email'                   => $data->get_email(),
			);

			foreach ( $meta as $key => $value ) {
				if ( ! empty( $value ) ) {
					update_post_meta( $post_id, $key, $value );
				}
			}

			$payment = new Pronamic_WP_Pay_Payment( $post_id );
		}

		return $payment;
	}
}
