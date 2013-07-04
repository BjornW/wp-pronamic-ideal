<div class="wrap">
	<h2 class="page-title"><?php echo get_admin_page_title(); ?></h2>
	<?php $wizard_names = Pronamic_WordPress_ConfigurationWizard_Factory::get_all_registered_wizards(); ?>


	<?php

	$ideal_basic_wizard = Pronamic_WordPress_ConfigurationWizard_Factory::get_wizard( 'idealbasic' );

	$ideal_basic_wizard->show();

	?>
</div>