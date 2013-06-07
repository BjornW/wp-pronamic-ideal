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
		
	}
	
	public function showEditFields( $data, $instance = 0 ) {
		?>
		<tr valign="top">
			
		</tr>
		<?php
	}
}