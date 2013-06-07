<?php

/**
 * AddOn Class for TheCartPress
 * 
 * @company Pronamic
 * @author Leon Rowland <leon@rowland.nl>
 * @copyright (c) 2013, Pronamic
 * @version 1.0
 */
class Pronamic_TheCartPress_IDeal_AddOn extends Pronamic_WordPress_IDeal_Extension {
	
	public static function bootstrap() {
		
		// Register the iDeal Plugin [no action required]
		tcp_register_payment_plugin( 'Pronamic_TheCartPress_IDeal_IDealGateway' );
	}
	
	public static function is_supported() {
		return (boolean) defined( 'TCP_FOLDER' );
	}
	
	
	public static function status_update( Pronamic_WordPress_IDeal_Payment $payment, $can_redirect = false ) {
		if ( $payment->getSource() === 'thecartpress' && self::is_supported() ) {
			$id = $payment->getSourceId();
		}
	}

	
}