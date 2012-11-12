<?php

/**
 * Title: Security
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Gateways_IDealAdvanced_Security {
	/**
	 * Indicator for the begin of an certificate
	 * 
	 * @var string
	 */
	const CERTIFICATE_BEGIN = '-----BEGIN CERTIFICATE-----';
	
	/**
	 * Indicator for the end of an certificate
	 * 
	 * @var string
	 */
	const CERTIFICATE_END = '-----END CERTIFICATE-----';

	//////////////////////////////////////////////////

	/**
	 * Get the sha1 fingerprint from the specified certificate
	 * 
	 * @param string $certificate
	 * @return fingerprint or null on failure
	 */
	public static function getShaFingerprint($certificate) {
		return self::getFingerprint($certificate, 'sha1');
	}

	/**
	 * Get the md5 fingerprint from the specified certificate
	 * 
	 * @param string $certificate
	 * @return fingerprint or null on failure
	 */
	public static function getMd5Fingerprint($certificate) {
		return self::getFingerprint($certificate, 'md5');
	}

	/**
	 * Get the fingerprint from the specified certificate
	 * 
	 * @param string $certificate
	 * @return fingerprint or null on failure
	 */
	public static function getFingerprint($certificate, $hash = null) {
		$fingerprint = null;

		// The openssl_x509_read() function will throw an warning if the supplied 
		// parameter cannot be coerced into an X509 certificate
		$resource = @openssl_x509_read($certificate);
		if($resource !== false) {
			$output = null;

			$result = openssl_x509_export($resource, $output);
			if($result !== false) {
				$output = str_replace(self::CERTIFICATE_BEGIN, '', $output);
				$output = str_replace(self::CERTIFICATE_END, '', $output);

				// Base64 decode
				$fingerprint = base64_decode($output);

				// Hash
				if($hash !== null) {
					$fingerprint = hash($hash, $fingerprint);
				}
			} else {
				// @todo what to do?
			}
		} else {
			// @todo what to do?
		}

		return $fingerprint;
	}

	//////////////////////////////////////////////////
    
    /**
    * function to sign a message
    * @param filename of the private key
    * @param message to sign
    * @return signature
    */
    
    public static function signMessage($privateKey, $privateKeyPassword, $data) {
        $signature = null;

		$resource = openssl_pkey_get_private($privateKey, $privateKeyPassword);
		if($resource !== false) {
			// Compute signature 
        	$computed = openssl_sign($data, $result, $resource);
        	if($computed) {
        		$signature = $result;
        	}

	        // Free the key from memory
        	openssl_free_key($resource);
		} else {
			// @todo what to do?
		}

        return $signature;
    }
    
    /**
    * function to verify a message
    * @param filename of the public key to decrypt the signature
    * @param message to verify
    * @param sent signature
    * @return signature
    */
    
    function verifyMessage($certfile, $data, $signature) {
        // $data and $signature are assumed to contain the data and the     signature
        $ok=0;
        // fetch public key from certificate and ready it
        $fp = fopen( dirname(__FILE__) . "/security/" . $certfile, "r");
        
        if(!$fp) {
          return false;
        }
        
        $cert = fread($fp, 8192);
        fclose($fp);
        $pubkeyid = openssl_get_publickey($cert);

        // state whether signature is okay or not
        $ok = openssl_verify($data, $signature, $pubkeyid);
        
        // free the key from memory
        openssl_free_key($pubkeyid);

        return $ok;
    }
   
    public static function test() {
		// Input
		$privateKeyPassword = 'Password';
		
		// DN
		$dn = array(
			'countryName' => 'NL' , 
			'stateOrProvinceName' => 'Friesland' , 
			'localityName' => 'Wijnjewoude' , 
			'organizationName' => 'Pronamic' ,
			'organizationalUnitName' => 'iDEAL team' , 
			'commonName' => 'Pronamic' , 
			'emailAddress' => 'info@pronamic.nl'
		);
		
		// Generate a new private key
		$privateKeyResource = openssl_pkey_new(array(
			'private_key_bits' => 1024 , 
			'private_key_type' => OPENSSL_KEYTYPE_RSA 
		));
		
		// Generate a new CSR (Certificate Signing Request) based on the information provided 
		// by dn, which represents the Distinguished Name to be used in the certificate.
		$csr = openssl_csr_new($dn, $privateKeyResource); 
		
		// Sign a CSR with another certificate (or itself) and generate a certificate
		$certificateResource = openssl_csr_sign($csr, null, $privateKeyResource, 365);
		
		// Export a certificate as a string
		// stores x509 into a string named by output in a PEM encoded format. 
		$certificateString = '';
		$result = openssl_x509_export($certificateResource, $certificateString);
		
		// Gets an exportable representation of a key into a string
		$privateKeyString = '';
		$result = openssl_pkey_export($privateKeyResource, $privateKeyString, $privateKeyPassword); 
    }
}