<?php

/**
 * Parent class for current and future extensions
 * for Pronamic IDeal
 * 
 * Includes some common methods that make it a valuable
 * usage on working new additions
 * 
 * @copyright (c) 2013, Pronamic
 * @company Pronamic
 * @author Leon Rowland <leon@rowland.nl>
 * @version 1.0
 */
abstract class Pronamic_WordPress_IDeal_Extension {
	
	/**
	 * Used as part of the loader to determine
	 * if an extension is enabled/supported for this
	 * usage
	 * 
	 * Return a boolean
	 * 
	 * @return bool
	 */
	abstract public static function is_supported();
	
	/**
	 * Used as a callback function to determine what to
	 * do with the passed in payment.
	 * 
	 * The payment will contain information related to
	 * its source ( which you should use as a conditional
	 * to check that its part of your extension )
	 * 
	 * @see Pronamic_WordPress_IDeal_Payment
	 * 
	 * @param Pronamic_WordPress_IDeal_Payment $payment
	 * @param bool $can_redirect
	 */
	abstract public static function status_update( Pronamic_WordPress_IDeal_Payment $payment, $can_redirect = false );
	
}