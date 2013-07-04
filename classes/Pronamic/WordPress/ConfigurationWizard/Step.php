<?php

/**
 * Pronamic_WordPress_ConfigurationWizard_Step
 * 
 * A step for the configuration wizard. Instantiating this class should not
 * be done. Instead this is used purely in the Wizard_Wizard class to return
 * an instance of this for you to register settings with.
 * 
 * @package ConfigurationWizard
 * @subpackage Step
 * 
 * @author Leon Rowland <leon@rowland.nl>
 */
class Pronamic_WordPress_ConfigurationWizard_Step {
	
	/**
	 * Holds the title for this step.  Is the unique title
	 * passed into the Wizard_Wizard::add_step() method.
	 * 
	 * Is used in the general display of the form wizard.
	 * 
	 * @var string
	 */
	private $title;
	
	/**
	 * Holds all the registered settings for this step.
	 * 
	 * All items in this array are part of the parent class
	 * Pronamic_WordPress_ConfigurationWizard_Field_Field
	 * 
	 * @var array
	 */
	private $settings = array();
	
	public function __construct( $title ) {
		$this->title = $title;
	}
	
	/**
	 * Returns the title set for this Wizard step.
	 * 
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}
	
	/**
	 * Registers a setting to be used in this specific step.  Requires 
	 * to be an instance of Pronamic_WordPress_ConfigurationWizard_Field_Field
	 * 
	 * @access public
	 * @param Pronamic_WordPress_ConfigurationWizard_Field_Field $field
	 * @return void
	 */
	public function add_setting( Pronamic_WordPress_ConfigurationWizard_Field_Field $field ) {
		$this->settings[] = $field;
	}
	
	/**
	 * Returns all settings registered for this step.
	 * 
	 * @access public
	 * @return array
	 */
	public function all_settings() {
		return $this->settings;
	}
	
}