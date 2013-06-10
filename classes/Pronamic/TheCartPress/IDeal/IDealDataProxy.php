<?php

class Pronamic_TheCartPress_IDeal_IDealDataProxy extends Pronamic_WordPress_IDeal_IDealDataProxy {
	
	public function __construct( $order ) {
		parent::__construct();
		$this->order = $order;
	}
	
	public function getSource() {
		return "thecartpress";
	}
	
	public function get_source_id() {
		return $this->order->order_id;
	}
	
	public function getDescription() {
		return sprintf( __( 'Order %s', 'pronamic_ideal' ), $this->getOrderId() );
	}
	
	public function getOrderId() {
		return $this->order->order_id;
	}
	
	public function getItems() {
		$items = new Pronamic_IDeal_Items();
		
		$item = new Pronamic_IDeal_Item();
		$item->setNumber( $this->getOrderId() );
		$item->setDescription( $this->getDescription() );
		$item->setPrice( Orders::getTotal( $this->order->order_id ) );
		$item->setQuantity( 1 );
		
		$items->addItem( $item );
		
		return $items;
	}
	
	public function getCurrencyAlphabeticCode() {
		return tcp_get_the_currency_iso();
	}
	
	public function getEMailAddress() {
		return $this->order->billing_email;
	}
	
	public function getCustomerName() {
		return $this->order->billing_firstname . ' ' . $this->order->billing_lastname;
	}
	
	public function getOwnerAddress() {
		return $this->order->billing_street;
	}
	
	public function getOwnerCity() {
		return $this->order->billing_city;
	}
	
	public function getOwnerZip() {
		return $this->order->billing_postcode;
	}
	
	public function getNormalReturnUrl() {
		$return_url = add_query_arg( array(
			'id' => $this->order->order_id,
			'key' => $this->get_entrance_code()
		), tcp_get_the_checkout_ok_url() );
		
		return $return_url;
	}
	
	public function getSuccessUrl() {
		return $this->getNormalReturnUrl();
	}
	
	public function getCancelUrl() {
		return '';
	}
	
	public function getErrorUrl() {
		return '';
	}
	
}