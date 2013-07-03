<?php

class Pronamic_WordPress_ConfigurationWizard_Field_Text extends Pronamic_WordPress_ConfigurationWizard_Field_Field {
	
	public function display() {
		?>
		<input type="text" name="<?php echo $this->get_setting_name(); ?>" value="<?php echo $this->get_value(); ?>" />
		<?php
	}
	
}