<?php

/**
 * Pronamic_WordPress_ConfigurationWizard_Field_Field
 * 
 * Extend this class to create custom fields for the 
 * Pronamic_WordPress_ConfigurationWizard_Step.
 * 
 * You can overide the set_value() method to have the saving
 * of the data custom to your preferences.
 * 
 * Pass into the constructor a $label_name and a $setting_name.
 * 
 * $label_name is used in the label creation of this perticular
 * setting field.
 * 
 * $setting_name is the name of the setting used in update_option
 * to save the value of the input to.
 * 
 * Display of the input is handled by the method display()
 * which is an abstract method that all child classes MUST implement.
 * 
 * @package ConfigurationWizard
 * @subpackage Field
 * 
 * @author Leon Rowland <leon@rowland.nl>
 */
abstract class Pronamic_WordPress_ConfigurationWizard_Field_Field {
	
	/**
	 * The label used for setting
	 * 
	 * @var string
	 */
	private $label_name;
	
	/**
	 * The name of the setting key in
	 * which the value is saved to.
	 * 
	 * @var string
	 */
	private $setting_name;
	
	/**
	 * Holds the saved setting value for
	 * this perticular setting name.
	 * 
	 * @var mixed
	 */
	private $setting_value;
		
	public function __construct( $label_name, $setting_name ) {
		$this->label_name = $label_name;
		$this->setting_name = $setting_name;
	}
	
	/**
	 * Returns the label name.
	 * 
	 * @access public
	 * @return string
	 */
	public function get_label_name() {
		return $this->label_name;
	}
	
	/**
	 * Returns the setting name
	 * 
	 * @access public
	 * @return string
	 */
	public function get_setting_name() {
		return $this->setting_name;
	}
	
	/**
	 * Sets the passed value to the already set $setting_name.
	 * 
	 * You can overide this method to handle the setting of your
	 * child field's value however you please.
	 * 
	 * @access public 
	 * @param mixed $value
	 * @return void
	 */
	public function set_value( $value ) {
		$this->setting_value = $value;
		update_option( $this->setting_name, $this->setting_value );
	}
	
	/**
	 * Gets the setting from the already set $setting_name.
	 * 
	 * You can overide this method to handle the setting of your
	 * childs field's value however you please.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function get_value() {
		if ( ! isset( $this->setting_value ) )
			$this->setting_value = get_option( $this->setting_name );
		
		return $this->setting_value;
	}
	
	/**
	 * Each child class must implement this method to handle the
	 * displaying of their field.
	 * 
	 * To get the name of the field, use the method self:get_setting_name()
	 * 
	 * The display method must echo out the contents.
	 * 
	 * @access public
	 * @return void
	 */
	public abstract function display();
	
}