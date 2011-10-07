<?php

/**
 * Title: WordPress iDEAL configuration
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_WordPress_IDeal_Configuration {
	/**
	 * Unique ID of configuration
	 * 
	 * @var string
	 */
	private $id;

	/**
	 * The iDEAL variant information
	 * 
	 * @var Pronamic_IDeal_Variant
	 */
	private $variant;

	/**
	 * The merchant ID
	 * 
	 * @var string
	 */
	private $merchantId;

	//////////////////////////////////////////////////

	public $subId;

	public $mode;

	public $hashKey;
	
	public $privateKey;
	
	public $privateKeyPassword;
	
	public $privateCertificate;

	//////////////////////////////////////////////////
	// Variables for generatoring private key and certficate
	//////////////////////////////////////////////////

	/**
	 * Number of days the certificate should be valid
	 * 
	 * @var int
	 */
	public $numberDaysValid;
	
	/**
	 * Country name
	 * 
	 * @var string
	 */
	public $country;
	
	/**
	 * State or province
	 * 
	 * @var string
	 */
	public $stateOrProvince;
	
	/**
	 * Locality name
	 * 
	 * @var string
	 */
	public $locality;
	
	/**
	 * Organization name
	 * 
	 * @var string
	 */
	public $organization;
	
	/**
	 * Organization unit name
	 * 
	 * @var string
	 */
	public $organizationUnit;

	/**
	 * Common name
	 * 
	 * @var string
	 */
	public $commonName;

	/**
	 * E-mail address
	 * 
	 * @var string
	 */
	public $eMailAddress;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an configuration
	 */
	public function __construct() {

	}

	//////////////////////////////////////////////////

	/**
	 * Get the ID of this configuration
	 * 
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set the ID of this configuration
	 * 
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the name of this configuration
	 * 
	 * @return string
	 */
	public function getName() {
		$name = '';

		$variant = $this->getVariant();
		if($variant != null) {			
			$name .= $variant->getName();
		}

		return $name;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the variant
	 * 
	 * @return string
	 */
	public function getVariant() {
		return $this->variant;
	}

	/**
	 * Set the variant
	 * 
	 * @param string $variant
	 */
	public function setVariant($variant) {
		$this->variant = $variant;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the merchant ID
	 * 
	 * @return string
	 */
	public function getMerchantId() {
		return $this->merchantId;
	}

	/**
	 * Set the merchant ID
	 * 
	 * @param $merchantId
	 */
	public function setMerchantId($merchantId) {
		$this->merchantId = $merchantId;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the sub ID
	 * 
	 * @return string
	 */
	public function getSubId() {
		return $this->subId;
	}

	/**
	 * Set the sub ID
	 * 
	 * @param string $subId
	 */
	public function setSubId($subId) {
		$this->subId = $subId;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the payment server URL
	 * 
	 * @return string
	 */
	public function getPaymentServerUrl() {
		$url = null;
		
		if($variant = $this->getVariant()) {
			$url = $variant->getPaymentServerUrl($this->mode);
		}
	
		return $url;
	}
	
	/**
	 * Get the provider dashboard URL
	 * 
	 * @return string
	 */
	public function getDashboardUrl() {
		$url = null;
		
		if($variant = $this->getVariant()) {
			$url = $variant->getDashboardUrl($this->mode);
		}
	
		return $url;
	}
}
