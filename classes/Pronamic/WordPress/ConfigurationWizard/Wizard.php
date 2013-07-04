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
	private $steps = array( );

	/**
	 * Holds the current step the wizard is at
	 * 
	 * @var int
	 */
	private $current_step;

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
		return $this->steps;
	}
	
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
		// Load the configuration wizard script
		wp_enqueue_script( 'ConfigurationWizard' );
		wp_enqueue_style( 'ConfigurationWizard' );
		
		?>
		<form method="post">
			<div class="pronamic_configuration_wizard_holder">
				<ul class="pronamic_configuration_wizard_steps">
					<?php foreach ( $this->get_all_steps() as $step ) : ?>
						<li><?php echo $step->get_title(); ?></li>
					<?php endforeach; ?>
				</ul>
				
				<?php foreach ( $this->get_all_steps() as $step_id => $step ) : ?>
				
				<div class="pronamic_configuration_wizard_step <?php if ( 0 == $step_id ) : ?> pronamic_configuration_wizard_current_step <?php endif; ?>" data-step="<?php echo $step_id; ?>">
						<h2 class="pronamic_configuration_wizard_step_title"><?php echo $step->get_title(); ?></h2>

						<?php foreach ( $step->all_settings() as $setting ) : ?>
							<div class="pronamic_configuration_wizard_step_setting">
								<label>
									<?php echo $setting->get_label_name(); ?>
									<?php echo $setting->display(); ?>
								</label>
							</div>
						<?php endforeach; ?>

					</div>
					
				<?php endforeach; ?>

				<div class="pronamic_configuration_wizard_navigation">
					<?php if ( $this->has_previous_step() ) : ?>
						<a class="pronamic_configuration_wizard_previous_step_button" href="#"><?php _e( 'Previous Step', 'pronamic_ideal' ); ?></a>
					<?php endif; ?>
					<?php if ( $this->has_next_step() ) : ?>
						<a class="pronamic_configuration_wizard_next_step_button" href="#"><?php _e( 'Next Step', 'pronamic_ideal' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</form>
		<?php
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
		if ( !isset( $this->current_step ) )
			$this->current_step = ( filter_has_var( INPUT_GET, 'step' ) ? filter_input( INPUT_GET, 'step', FILTER_VALIDATE_INT ) : 0 );

		return $this->current_step;
	}

	/**
	 * Returns a bool if this wizard has another step
	 * after the current one.
	 * 
	 * @access public
	 * @return bool
	 */
	public function has_next_step() {
		$current_step = $this->get_current_step();

		$next_step = ++$current_step;

		return ( array_key_exists( $next_step, $this->steps ) );
	}

	/**
	 * Returns the next step number.  Relates the array key for all
	 * steps registered.
	 * 
	 * @access public
	 * @return int
	 */
	public function get_next_step() {
		$current_step = $this->get_current_step();
		return++$current_step;
	}

	/**
	 * Returns a bool if this wizard has a previous step
	 * before the current one.
	 * 
	 * @access public
	 * @return bool
	 */
	public function has_previous_step() {
		$current_step = $this->get_current_step();

		$previous_step = --$current_step;

		return ( array_key_exists( $previous_step, $this->steps ) );
	}

	/**
	 * Returns the previous step number. Relates to the array key for all
	 * steps registered.
	 * 
	 * @access public
	 * @return int
	 */
	public function get_previous_step() {
		$current_step = $this->get_current_step();
		return--$current_step;
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

	/**
	 * Returns the current url the form is on. Perhaps a better
	 * solution is needed for this.
	 * 
	 * @access public
	 * @return string
	 */
	public function get_current_url() {
		$host = ( filter_has_var( INPUT_SERVER, 'HTTPS' ) ? 'https://' : 'http://' );

		return $host . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

}