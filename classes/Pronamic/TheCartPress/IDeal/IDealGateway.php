<?php

class Pronamic_TheCartPress_IDeal_IDealGateway extends TCP_Plugin {
	
	public function getTitle() {
		return __( 'Pronamic IDeal', 'pronamic_ideal' );
	}
	
	public function getIcon() {
		return false;
	}
	
	public function getDescription() {
		return __( 'Pronamic IDeal Integration with TheCartPress <br>Author: <a href="http://pronamic.nl">Pronamic</a>', 'pronamic_ideal' );
	}
	
	public function getCheckoutMethodLabel( $instance, $shippingCountry = '', $shoppingCart = false ) {
		$data = tcp_get_payment_plugin_data( get_class( $this ), $instance );
		
		if ( isset( $data['title'] ) ) {
			$title = $data['title'];
		} else {
			$title = $this->getTitle();
		}
		
		return $title;
	}
	
	public function showEditFields( $data ) {
		if ( ! isset( $data['configuration_id'] ) )
			$data['configuration_id'] = null;
		
		?>
		<tr valign="top">
			<th scope="row">
				<label for="configuration_id"><?php _e( 'Configuration', 'pronamic_ideal' ); ?></label>
			</th>
			<td>
				<select name="configuration_id">
					<?php foreach ( Pronamic_WordPress_IDeal_IDeal::get_configurations_select_options() as $configuration_id => $configuration ) : ?>
					<option value="<?php echo $configuration_id; ?>" <?php selected( $data['configuration_id'], $configuration_id ); ?>><?php echo $configuration; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}
	
	public function saveEditFields( $data, $instance = 0 ) {
		$data['configuration_id'] = isset( $_POST['configuration_id'] ) ? $_POST['configuration_id'] : '';
		return $data;
	}
	
	public function showPayForm( $instance, $shippingCountry, $shoppingCart, $order_id = 0 ) {
		
		// Get saved data for this TheCartPress instance
		$data = tcp_get_payment_plugin_data( get_class( $this ), $instance );
		
		// Get the configuration ID
		$configuration_id = null;
		
		// Check the configuration ID is set
		if ( isset( $data['configuration_id'] ) )
			$configuration_id = $data['configuration_id'];
		
		// DOnt show form if no configuration id
		if ( ! $configuration_id )
			return;

		// Get the order
		$order = Orders::get( $order_id );
		
		// Build the data
		$ideal_data = new Pronamic_TheCartPress_IDeal_IDealDataProxy( $order );
		
		// Get the entire configuration
		$configuration = Pronamic_WordPress_IDeal_ConfigurationsRepository::getConfigurationById( $configuration_id );
		
		// Get the selected gateway for this configuration
		$gateway = Pronamic_WordPress_IDeal_IDeal::get_gateway( $configuration );
		
		if ( $gateway ) {
			Pronamic_WordPress_IDeal_IDeal::start( $configuration, $gateway, $ideal_data );
			
			?>
		
			<?php if ( $gateway->is_html_form() ) : ?>
				<?php $gateway->redirect_via_html(); ?>
			<?php endif; ?>
		
			<?php if ( $gateway->is_http_redirect() ) : ?>
				<a href="<?php echo $gateway->get_action_url(); ?>"><?php _e( 'Pay', 'pronamic_ideal' ); ?></a>
			<?php endif; ?>
			<?php
		}
	}
}