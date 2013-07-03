<?php

/**
 * Pronamic Configuration Wizard
 * 
 * Make a new instance of this class and pass in a unique identifier
 * string for your instance of the wizard, and a title to be used
 * in the menu and throughout the steps.
 * 
 * 
 * 
 */
class Pronamic_WordPress_ConfigurationWizard_Wizard {

	/**
	 * Holds the unique name for this instance
	 * of the wizard
	 * 
	 * @var string
	 */
	private $unique_name;
	
	/**
	 * Holds the clean formatted string for 
	 * this instance of the wizard
	 * 
	 * @var string
	 */
	private $title;
	
	/**
	 * Holds all the steps registered for this wizard
	 * 
	 * @var array Pronamic_WordPress_ConfigurationWizard_Step
	 */
	private $steps = array();

	/**
	 * Pass in the unique name for this wizard. It will be used
	 * later on to ensure the correct wizard is loaded.
	 * 
	 * @action admin_init | func:listen 
	 * 
	 * @access public
	 * @param string $unique_name
	 * @param string $title
	 * @return void
	 */
	public function __construct( $unique_name, $title ) {
		$this->unique_name = $unique_name;
		$this->title = $title;
		
		add_action( 'admin_init', array( $this, 'listen' ) );
	}
	
	/**
	 * Returns the unique name
	 * 
	 * @access public
	 * @return string
	 */
	public function get_unique_name() {
		return $this->unique_name;
	}
	
	/**
	 * Returns the clean title
	 * 
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Adds a step to this wizard.  Will return an instance
	 * of Pronamic_WordPress_ConfigurationWizard_Step where you 
	 * will further add settings to.
	 * 
	 * @see Pronamic_WordPress_ConfigurationWizard_Step
	 * 
	 * @access public
	 * @param string $unique_name
	 * @return Pronamic_WordPress_ConfigurationWizard_Step
	 */
	public function add_step( $unique_name ) {
		$total_steps = $this->get_total_steps();
		
		// Make a new instance of a Wizard_Step and stores with the passed unique name.
		$this->steps[$total_steps] = new Pronamic_WordPress_ConfigurationWizard_Step( $unique_name );
		
		// Returns the associated instance
		return $this->steps[$total_steps];
	}

	/**
	 * Returns the registered instance of the 
	 * Pronamic_WordPress_ConfigurationWizard_Step if one exists.
	 * 
	 * @access public
	 * @param string $unique_name
	 * @return Pronamic_WordPress_ConfigurationWizard_Step
	 */
	public function get_step( $unique_name ) {
		if ( array_key_exists( $unique_name, $this->steps ) )
			return $this->steps[$unique_name];
	}

	/**
	 * Returns an array of all the registered steps unique
	 * names
	 * 
	 * @access public
	 * @return array 
	 */
	public function get_all_steps() {
		return array_keys( $this->steps );
	}
	
	/**
	 * Listens to the get variables for knowledge about which step the user
	 * is currently on.  
	 */
	public function listen() {
		
	}
	
	/**
	 * Renders the wizard.  Can also return the wizard from the output
	 * buffer if true is passed into the parameter.
	 * 
	 * @access public
	 * @param bool $return
	 */
	public function show( $return = false ) {
		$current_step = $this->get_current_step();
		
		foreach ( $this->steps[$current_step]->all_settings() as $setting ) {
			echo $setting->get_label_name();
			echo $setting->display();
		}
	}
	
	/**
	 * Returns the current step based off a query variable 'step'.
	 * 
	 * If no query variable found, returns 0 ( the starting array key )
	 * 
	 * @access public
	 * @return int
	 */
	public function get_current_step() {
		return ( filter_has_var( INPUT_GET, 'step' ) ? filter_input( INPUT_GET, 'step', FILTER_VALIDATE_INT ) : 0 );
	}
	
	/**
	 * Returns the total number of steps registered with this wizard.
	 * 
	 * @access public
	 * @return int
	 */
	public function get_total_steps() {
		return count( $this->steps );
	}

}