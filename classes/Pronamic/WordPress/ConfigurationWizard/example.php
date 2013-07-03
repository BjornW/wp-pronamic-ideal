<?php

// builds the wizard instance
$ideal_wizard = pronamic_configuration_wizard( $name );

/**
 * add_page description
 * 
 * requires title
 * requires a set of inputs that can be translated to existing settings
 * 
 * 
 * add_step( $title );
 * 
 * returns instance of wizard_page
 * 
 * add_setting( $title, $setting_name, $input_type, $rules = 'pipe|delimited|rule|line' );
 * add_setting( $title, $setting_name, $input_type, $rules );
 * 
 * 
 * add_page( $title );
 * 
 * 
 * loop through all registered pages
 * 
 */


/**
 * register wizard
 * 
 * add_action( 'register_wizard', function() {
 * 
 *     $ideal_wizard = new Pronamic_WordPress_ConfigurationWizard_Wizard( 'iDeal Easy' );
 * 
 * 
 *	$basic_configuration_step = $ideal_wizard->add_step( 'Basic Configuration' );
 *  
 *	$basic_configuration_step->add_setting( 'iDeal Username', 'pronamic_ideal_easy_username' );
 *	$basic_configuration_step->add_setting( 'iDeal etc etc', 'pronamic_ideal_easy_etcetc' );
 *  * 
 *  Pronamic_WordPress_ConfigurationWizard_Factory::register_wizard( $ideal_wizard );
 * 
 * });
 */

/**
 * hmm. support for more than just settings may require the wizard class
 * to go back to being abstract. extending the class with the methods 
 * you want to represent each step is perhaps better.
 * 
 * this does mean a lot of duplicate code for things like saving settings
 * across configuration steps.
 * 
 * perhaps, the settings fields ( 3rd param in add_setting ) can be a 
 * class, and being able to set custom fields will allow the setting
 * of the database values while allowing custom forms of input.
 */