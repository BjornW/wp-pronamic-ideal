<?php

class Pronamic_WordPress_ConfigurationWizard_Field_Variant extends Pronamic_WordPress_ConfigurationWizard_Field_Field {

	public function display() {
		?>

		<select id="pronamic_ideal_variant_id" name="pronamic_ideal_variant_id">
			<option value=""></option>
			<?php foreach ( Pronamic_WordPress_IDeal_ConfigurationsRepository::getProviders() as $provider ) : ?>
				<optgroup label="<?php echo $provider->getName(); ?>">
					<?php foreach ( $provider->getVariants() as $variant ) : ?>
						<option data-ideal-method="<?php echo $variant->getMethod(); ?>" value="<?php echo $variant->getId(); ?>"><?php echo $variant->getName(); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>

		<?php
	}

}