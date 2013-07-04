<?php

class Pronamic_WordPress_ConfigurationWizard_Field_BankImage extends Pronamic_WordPress_ConfigurationWizard_Field_Field {
	
	public $chosen_bank = '';
	
	public function display() {
		
		$banks = array(
			'abnamro'  => 'abn-amro-logo.gif',
			'adyen'    => 'adyen-logo.png',
			'buckaroo' => 'buckaroo-logo.png',
			'deutschebank' => 'deutschebank-logo.png',
			'dutchpaymentgroup' => 'dutchpaymentgroup-logo.png',
			'easy-ideal' => 'easyideal-logo.png',
			'frieslandbank' => 'frieslandbank-logo.gif',
			'icepay' => 'icepay-logo.png',
			'ing' => 'ing-logo.png',
			'mollie' => 'mollie-logo.png',
			'ogone' => 'ogone-logo.gif',
			'rabobank' => 'rabobank-logo.png',
			'sisow' => 'sisow-logo.png',
			'targetpay' => 'targetpay-logo.png',
			'qantani' => 'qantani-logo.png'
		);
		
		?>

		<style type="text/css">
			.pronamic_configuration_bank_images li {
				padding:10px;
				margin-bottom:5px;
				float:left;
			}
				.pronamic_configuration_bank_images li img {
					display:block;
					margin:0 auto;
				}

			.pronamic_configuration_bank_images .selected {
				border:1px solid #f9461c;
				border-radius:10px;
				-moz-border-radius:10px;
				-webkit-border-radius:10px;
				padding:9px;
			}

			.pronamic_configuration_bank_images_holder:before,
			.pronamic_configuration_bank_images_holder:after {
				content: " "; /* 1 */
				display: table; /* 2 */
			}

			.pronamic_configuration_bank_images_holder:after {
				clear: both;
			}
			
			.pronamic_configuration_bank_images_holder {
				*zoom: 1;
			}
		</style>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.pronamic_configuration_wizard_form').on('LoadStep0', function() {
				jQuery('.pronamic_configuration_bank_images img').click(function() {
					var providerId = jQuery(this).data('provider');
					jQuery('.pronamic_configuration_chosen_bank').val(providerId);
					
					Pronamic_ConfigurationWizard.step.registerData('chosen_bank',providerId);

					jQuery('.pronamic_configuration_bank_images img').parent('li').removeClass('selected');
					jQuery(this).parent('li').addClass('selected');
				});
			});
			
			jQuery('.pronamic_configuration_wizard_form').on('LoadStep1', function() {
				console.log(Pronamic_ConfigurationWizard.step.get('chosen_bank', 0));
			});
		});
		</script>
		<div class="pronamic_configuration_bank_images_holder">
			<ul class="pronamic_configuration_bank_images">
				<?php foreach ( Pronamic_WordPress_IDeal_ConfigurationsRepository::getProviders() as $provider ) : ?>
					<?php if ( array_key_exists( $provider->getId(), $banks ) ) : ?>
						<li>
							<img title="<?php echo $provider->getName(); ?>" height="30" data-provider="<?php echo $provider->getId(); ?>" src="<?php echo plugins_url( "admin/images/wizard/{$banks[$provider->getId()]}", Pronamic_WordPress_IDeal_Plugin::$file ); ?>">
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<input class="pronamic_configuration_chosen_bank" type="hidden" name="<?php echo $this->get_setting_name(); ?>" value="<?php echo $this->get_value(); ?>"/>
		</div>
		<?php
	}
	
	public function set_value( $value ) {
		$this->chosen_bank = $value;
	}
	
	public function get_value() {
		return $this->chosen_bank;
	}
	
}