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
		
		add_action( 'pronamic_ideal_status_update', array( __CLASS__, 'status_update' ), 10, 2 );
		add_filter( 'pronamic_ideal_source_column_thecartpress', array( __CLASS__, 'source_column' ), 10, 2 );
	}
	
	public static function is_supported() {
		return (boolean) defined( 'TCP_FOLDER' );
	}
	
	
	public static function status_update( Pronamic_WordPress_IDeal_Payment $payment, $can_redirect = false ) {
		if ( $payment->getSource() === 'thecartpress' && self::is_supported() ) {
			
			// Get the order id
			$order_id = $payment->getSourceId();
			
			// Get the order
			$order = Orders::get( $order_id );
			
			// Build the data
			$order_data = new Pronamic_TheCartPress_IDeal_IDealDataProxy( $order );
			
			
			$url = $order_data->getNormalReturnUrl();
			
			switch( $payment->status ) {
				case Pronamic_Gateways_IDealAdvanced_Transaction::STATUS_CANCELLED:
					
					Orders::editStatus( $order_id, 'CANCELLED', $payment->getId(), __( 'iDEAL payment cancelled.', 'pronamic_ideal' ) );
					
					break;
				
				case Pronamic_Gateways_IDealAdvanced_Transaction::STATUS_EXPIRED:
					
					// Line 403 in /plugins/paypal/TCPPayPal.php. Uses PROCESSING for
					// expired. hmm. Anoter check shows a few lines below that it goes
					// through another case in the switch statement. This time setting
					// it to cancelled.
					Orders::editStatus( $order_id, 'CANCELLED', $payment->getId(), __( 'iDEAL payment expired.', 'pronamic_ideal' ) );
					
					break;
					
				case Pronamic_Gateways_IDealAdvanced_Transaction::STATUS_FAILURE:
					
					Orders::editStatus( $order_id, 'CANCELLED', $payment->getId(), __( 'iDEAL payment cancelled.', 'pronamic_ideal' ) );
					
					
					break;
					
				case Pronamic_Gateways_IDealAdvanced_Transaction::STATUS_SUCCESS:
					
					Orders::editStatus( $order_id, 'COMPLETED', $payment->getId(), __( 'iDEAL payment completed.', 'pronamic_ideal' ) );
					
					
					$url = $order_data->getSuccessUrl();
					
					break;
					
				case Pronamic_Gateways_IDealAdvanced_Transaction::STATUS_OPEN:
					
					Orders::edit( $order_id, $order->status, $order->code_tracking, __( 'iDEAL payment open.', 'pronamic_ideal' ) );
					
					
					break;
					
				default:
					
					Orders::edit( $order_id, $order->status, $order->code_tracking, __( 'iDEAL payment unknown.', 'pronamic_ideal' ) );
					
					
					break;
			}
			
			if ( $can_redirect ) {
				wp_redirect( $url, 303 );
				exit;
			}
				
		}
	}
	
	public static function source_column( $text, $payment ) {
		$text  = __( 'TheCartPress', 'pronamic_ideal' ) . '<br/>';
		$text .= sprintf( __( "Order #%s", 'pronamic_ideal' ), $payment->getSourceId() );
				
		return $text;
	}

	
}