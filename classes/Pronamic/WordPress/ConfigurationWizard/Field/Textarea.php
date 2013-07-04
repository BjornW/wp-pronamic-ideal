<?php

class Pronamic_WordPress_ConfigurationWizard_Field_Textarea extends Pronamic_WordPress_ConfigurationWizard_Field_Field {
	
	public function display() {
		
		?>
		
<textarea name="<?php echo $this->get_setting_name(); ?>"><?php echo $this->get_setting_value(); ?></textarea>

		<?php
		
	}
	
	
}