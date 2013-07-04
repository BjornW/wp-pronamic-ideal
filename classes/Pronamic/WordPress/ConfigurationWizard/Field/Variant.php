<?php

class Pronamic_WordPress_ConfigurationWizard_Field_Variant extends Pronamic_WordPress_ConfigurationWizard_Field_Field {

	public function display() {
		
		?>

		<script type="text/javascript">
			
			jQuery(document).ready(function() {
				var chosen_bank = jQuery('.pronamic_configuration_chosen_bank').val();
			});
			
		</script>

		<?php
	}

}