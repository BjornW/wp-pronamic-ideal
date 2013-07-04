<?php

class Pronamic_WordPress_ConfigurationWizard_Field_Blank extends Pronamic_WordPress_ConfigurationWizard_Field_Field {
	
	public function display() {
		
		?>

		<p class="description"><?php echo $this->get_setting_name(); ?></p>

		<?php
		
	}
	
	public function set_value( $value ) {
		
		// do nothing.
		
	}
	
}