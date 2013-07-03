<?php

class Pronamic_WordPress_ConfigurationWizard_Factory {
	
	private static $wizards = array();
	
	public static function register_wizard( Pronamic_WordPress_ConfigurationWizard_Wizard $wizard ) {
		self::$wizards[$wizard->get_unique_name()] = $wizard;
	}
	
	public static function get_wizard( $unique_name ) {
		if ( array_key_exists( $unique_name, self::$wizards ) )
			return self::$wizards[$unique_name];
	}
	
	public static function get_all_registered_wizards() {
		return self::$wizards;
	}
	
	public static function get_all_registered_wizards_titles() {
		return array_keys( self::$wizards );
	}
	
}