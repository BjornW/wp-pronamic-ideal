<?php

class Pronamic_WordPress_ConfigurationWizard_Step {
	
	private $title;
	
	private $settings = array();
	
	public function __construct( $title ) {
		$this->title = $title;
	}
	
	public function add_setting( Pronamic_WordPress_ConfigurationWizard_Field_Field $field ) {
		$this->settings[] = $field;
	}
	
	public function all_settings() {
		return $this->settings;
	}
	
}