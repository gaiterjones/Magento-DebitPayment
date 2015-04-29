DebitPayment
============
This is a fork of DebitPayment by Rouven Alexander Rieker & Itab


Changes
-----------


WORKAROUND - fixed invalid DebitType causing SEPA info in admin not to be shown

ADDED - Validation of IBAN in Admin

ADDED - Manual lookup of BIC if not present in SEPA data



Description
-----------
This extension allows shop owners to provide the payment method "DebitPayment" to their customers.
This includes:
- Complete order via DebitPayment
- Choose between DebitPayment via normal bank data or via SEPA data
- Find the correct German bank name given by the entered routing number
- Save account data encrypted in database to pre-fill checkout fields on further checkouts
- Export all DebitPayment orders as CSV file or DTAUS file
- SEPA Mandate PDF generation

Requirements
------------
- PHP >= 5.3.0
- PHP class [ZipArchive](http://php.net/manual/en/class.ziparchive.php)

Compatibility
-------------
- Magento >= 1.6
- Versions below should work to version 1.4 without any problems but it is not actively tested.

Installation Instructions
-------------------------
1. Install the extension via Magento Connect with the key shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. You can now enable the payment method via *System -> Configuration -> Sales -> Payment -> DebitPayment*

Uninstallation
--------------
To uninstall this extension you need to run the following SQL after removing the extension files:
```sql
  DELETE FROM `core_config_data` WHERE path LIKE 'payment/debit/%';
  DELETE FROM `core_config_data` WHERE path LIKE 'debitpayment/%';
  DELETE FROM `core_resource` WHERE code = 'debit_setup';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_acount_update';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_acount_name';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_acount_number';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_acount_blz';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_account_swift';
  DELETE FROM `eav_attribute` WHERE attribute_code = 'debit_payment_account_iban';
  DROP TABLE `debit_order_grid`;
```




Developer
---------
Rouven Alexander Rieker
- Website: [http://rouven-rieker.com](http://rouven-rieker.com)
- Twitter: [@therouv](https://twitter.com/therouv)

Licence
-------
[Open Software License (OSL 3.0)](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2008-2014 Rouven Alexander Rieker / ITABS GmbH
