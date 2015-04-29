<?php
/**
 * This file is part of the Itabs_Debit module.
 *
 * PHP version 5
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  Itabs/PAJ
 * @package   Itabs_Debit
 * @author    ITABS GmbH <info@itabs.de>
 * @copyright 2008-2014 ITABS GmbH (http://www.itabs.de)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   1.0.0 (1.1.4)
 * @link      http://www.magentocommerce.com/magento-connect/debitpayment.html
 */
/**
 * Debit Model
 */
class Itabs_Debit_Model_Debit extends Mage_Payment_Model_Method_Abstract
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'debit';

    /**
     * payment form block
     *
     * @var string MODULE/BLOCKNAME
     */
    protected $_formBlockType = 'debit/form';

    /**
     * payment info block
     *
     * @var string MODULE/BLOCKNAME
     */
    protected $_infoBlockType = 'debit/info';

    /**
     * @var bool Allow capturing for this payment method
     */
    protected $_canCapture = true;

    /**
     * @var bool Allow partial capturing for this payment method
     */
    protected $_canCapturePartial = true;

    /**
     * Assigns data to the payment info instance
     *
     * @param  Varien_Object|array $data Payment Data from checkout
     * @return Itabs_Debit_Model_Debit Self.
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        // Fetch routing number
        $ccType = $data->getDebitCcType();
        if (!$ccType) {
            $ccType = $data->getCcType();
        }
        $ccType = Mage::helper('debit')->sanitizeData($ccType);
        $ccType = $info->encrypt($ccType);

        // Fetch account holder
        $ccOwner = $data->getDebitCcOwner();
        if (!$ccOwner) {
            $ccOwner = $data->getCcOwner();
        }

        // Fetch account number
        $ccNumber = $data->getDebitCcNumber();
        if (!$ccNumber) {
            $ccNumber = $data->getCcNumber();
        }
        $ccNumber = Mage::helper('debit')->sanitizeData($ccNumber);
        $ccNumber = $info->encrypt($ccNumber);

        // Fetch the account swift
        $swift = $data->getDebitSwift();
        if ($swift) {
            $swift = $info->encrypt($swift);
        }

        // Fetch the account iban
        $iban = $data->getDebitIban();
        if ($iban) {
            $iban = $info->encrypt($iban);
        }

        $bankName = $data->getDebitBankname();

        // Set account data in payment info model
        $info->setCcType($ccType)                     // BLZ
             ->setCcOwner($ccOwner)                   // Kontoinhaber
             ->setCcNumberEnc($ccNumber)              // Kontonummer
             ->setDebitSwift($swift)                  // SWIFT Code
             ->setDebitIban($iban)                    // IBAN
             ->setDebitBankname($bankName)                    // IBAN
             ->setDebitType(Mage::helper('debit')->getDebitType());

        return $this;
    }

    /**
     * Returns the custom text for this payment method
     *
     * @return string Custom text
     */
    public function getCustomText()
    {
        return $this->getConfigData('customtext');
    }

    /**
     * Returns the account name from the payment info instance
     *
     * @return string Name
     */
    public function getAccountName()
    {
        $info = $this->getInfoInstance();

        return $info->getCcOwner();
    }

    /**
     * Returns the account number from the payment info instance
     *
     * @return string Number
     */
    public function getAccountNumber()
    {
        $info = $this->getInfoInstance();
        $data = $info->getCcNumberEnc();
        if (!is_numeric($data)) {
            $data = $info->decrypt($data);
        }
        if (!is_numeric($data)) {
            $data = $info->decrypt($data);
        }

        return $data;
    }

    /**
     * Returns the account blz from the payment info instance
     *
     * @return string BLZ
     */
    public function getAccountBLZ()
    {
        $info = $this->getInfoInstance();
        $data = $info->getCcType();
        if (!is_numeric($data)) {
            $data = $info->decrypt($data);
        }

        return $data;
    }

    /**
     * Returns the account bankname if applicable from the payment info instance
     *
     * @return string Bankname/Error
     */
    public function getAccountBankname()
    {
        $info = $this->getInfoInstance();
        $data = $info->getDebitBankname();

        return $data;
    }

    /**
     * Returns the account swift code from the payment info instance
     *
     * @return string SWIFT
     */
    public function getAccountSwift()
    {
        $info = $this->getInfoInstance();
        $data = $info->decrypt($info->getDebitSwift());

        return $data;
    }

    /**
     * Returns the account iban from the payment info instance
     *
     * @return string IBAN
     */
    public function getAccountIban()
    {
        $info = $this->getInfoInstance();
        $data = $info->decrypt($info->getDebitIban());

        return $data;
    }

    /**
     * Returns the encrypted data for mail
     *
     * @param  string $data Data to crypt
     * @return string Crypted data
     */
    public function maskBankData($data)
    {
        $crypt = str_repeat('*', strlen($data)-3) . substr($data, -3);

        return $crypt;
    }

    /**
     * Returns the encrypted data for mail
     *
     * @param  string $data Data to crypt
     * @return string Crypted data
     */
    public function maskSepaData($data)
    {
        $crypt = substr($data, 0, 3) . str_repeat('X', strlen($data)-7) . substr($data, -4);

        return $crypt;
    }
	
	public function validateIBAN($_iban)
	{
		require_once Mage::getModuleDir('', 'Itabs_Debit') . DS . 'etc'. DS . 'php-iban'. DS. 'php-iban.php';
		
		if(verify_iban($_iban)) { // valid IBAN
			return true;
		}		
		
		return false;
	}

	public function getBICFromIBAN($_iban)
	{
		require_once Mage::getModuleDir('', 'Itabs_Debit') . DS . 'etc'. DS . 'php-iban'. DS. 'php-iban.php';
		$_blz=trim(iban_get_bank_part($_iban));

		$file = $this->getFilePath();		
		
		// Open file
	    $fp = fopen($file, 'r');
	    
		while ($data = fgetcsv($fp, 1024, ",")) {
			if ($data[0] == $_blz) {
				$_bic = $data[2];
			}
	    }
		
		return $_bic;		
		
	}
	
	public function getBICFromBankName($_bankName)
	{
		$_bic=false;
		
		$file = $this->getFilePath();		
		
		// Open file
	    $fp = fopen($file, 'r');
	    
		while ($data = fgetcsv($fp, 1024, ",")) {
			if ($data[1] == $_bankName) {
				$_bic = $data[2];
			}
	    }
		
		return $_bic;
	}

	 /**
     * Get the path of the file "de.csv"
     *
     * @return  string
     */
	private function getFilePath()
	{
		$f = dirname(__FILE__);			// Get the path of this file
		$f = substr($f, 0, -5);			// Remove the "Model" dir
		$f = $f.'etc/bankdata/';		// Add the "etc" dir
		$f = $f.'de.csv';				// Add the filename
		$f = str_replace("\\","/",$f);	// change slashes
		
		//echo $f; exit;
		
		return $f;
	}	
}
