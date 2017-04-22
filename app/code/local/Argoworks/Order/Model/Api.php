<?php 
class Argoworks_Order_Model_Api extends Mage_Api_Model_Resource_Abstract{
	
	public function createCustomer($data = array()) {
	
		try {
			$customerId = 0;
			Mage::register('isSecureArea', 1);
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			$website = Mage::getModel('core/website')->load(1,'is_default');
			$websiteId = $website->getId();
			$customer = Mage::getModel('customer/customer');
			if ($websiteId) {
				$customer->setWebsiteId($websiteId);
			}
			$customer->loadByEmail($data['email']);
			if ($customer->getId()) {
				return $customer->getId();
			}
			$customer = Mage::getModel("customer/customer");
			$customer->setWebsiteId($websiteId)
				->setFirstname($data['firstname'])
				->setLastname($data['lastname'])
				->setEmail($data['email'])
				->setPassword($customer->generatePassword(12));
			
			try{
				$customer->save();
				if(@$data['address']){
					if(strlen(@$data['address']['country']) > 2){
						$data['address']['country'] = $this->convertToCountryCode($data['address']['country']);
					}
					$regionId = 0;
					$regionCollection = Mage::getModel('directory/region_api')->items($data['address']['country']);
					if($regionCollection){
						foreach($regionCollection as $region){
							$name = $region['name'];
							$code = $region['code'];
							if(strtolower($region['code']) == strtolower($data['address']['region']) || strtolower(str_replace(' ','',$region['name'])) == strtolower(str_replace(' ','',$data['address']['region']))){
								$regionId = $region['region_id'];
							}
						}
					}
					$address = Mage::getModel("customer/address");
					$address->setCustomerId($customer->getId())
						->setFirstname($customer->getFirstname())
						->setLastname($customer->getLastname())
						->setCountryId(@$data['address']['country'])
						->setRegionId($regionId)
						->setPostcode(@$data['address']['postcode'])
						->setCity(@$data['address']['city'])
						->setTelephone(@$data['address']['telephone'])
						->setStreet(@$data['address']['street'])
						->setIsDefaultBilling('1')
						->setSaveInAddressBook('1');
					
					try{
						$address->save();
						$customer->sendNewAccountEmail();
					}catch (Exception $e) {
						return $e->getMessage();
					}
				}
			}catch (Exception $e) {
				return $e->getMessage();
			}
			return $customer->getId();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function addQuoteProduct($quote, $product, $quantity, $storeId){
		$item = Mage::getModel('sales/quote_item');
		$item->setQuote($quote);
		$item->setStoreId($storeId);
		$item->setOptions($product->getCustomOptions())->setProduct($product);
		$quote->addItem($item);
		$item->addQty($quantity);
		$items = array($item);
		Mage::dispatchEvent('sales_quote_product_add_after', array('items' => $items));
	}
	
	public function createOrder($data = array()) {
		
		try {
			Mage::register('isSecureArea', 1);
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			$website = Mage::getModel('core/website')->load(1,'is_default');
			$websiteId = $website->getId();
			$storeId = Mage::app()->getWebsite($websiteId)->getDefaultGroup()->getDefaultStoreId();
			$quote = Mage::getModel('sales/quote')
					->setStoreId($storeId);
			$customer = Mage::getModel('customer/customer')
					->setWebsiteId($websiteId)
					->load($data['customer_id']);
			$quote->assignCustomer($customer);
			foreach($data['products'] as $product_info){
				$product = Mage::getModel('catalog/product')->load($product_info['product_id']);
				$this->addQuoteProduct($quote, $product, $product_info['quantity'], $storeId);
			}
			$addressData = array();
			if(!empty($data['ShipTo'])){
				$shipTo = $data['ShipTo'];
				$customerTelephone = '';
				if(!empty($shipTo['Contact']['Phones']['ContactPhone']['number'])){
					$customerTelephone = @$shipTo['Contact']['Phones']['ContactPhone']['number'];
				}else{
					if(!empty($shipTo['Customer']['Contact']['Phones']['ContactPhone'])){
						$customerTelephone = $shipTo['Contact']['Phones']['ContactPhone'][0]['number'];
					}
				}
				$customerAddress1 = @$shipTo['Contact']['Addresses']['ContactAddress']['address1'];
				$customerAddress2 = @$shipTo['Contact']['Addresses']['ContactAddress']['address2'];
				$customerCity = @$shipTo['Contact']['Addresses']['ContactAddress']['city'];
				$customerRegion = @$shipTo['Contact']['Addresses']['ContactAddress']['state'];
				$customerPostcode = @$shipTo['Contact']['Addresses']['ContactAddress']['zip'];
				$customerCountry = @$shipTo['Contact']['Addresses']['ContactAddress']['country'];
				$customerStreet = array($customerAddress1, $customerAddress2);
				if(strlen(@$customerCountry) > 2){
					$customerCountry = $this->convertToCountryCode($customerCountry);
				}
				$regionId = 0;
				$regionCollection = Mage::getModel('directory/region_api')->items($customerCountry);
				if($regionCollection){
					foreach($regionCollection as $region){
						$name = $region['name'];
						$code = $region['code'];
						if(strtolower($region['code']) == strtolower($customerRegion) || strtolower(str_replace(' ','',$region['name'])) == strtolower(str_replace(' ','',$customerRegion))){
							$regionId = $region['region_id'];
						}
					}
				}
				$addressData = array(
						
					'firstname' => $shipTo['firstName'],
					'lastname' => $shipTo['lastName'],
					'street' => $customerStreet,
					'city' => $customerCity,
					'postcode' => $customerPostcode,
					'telephone' => $customerTelephone,
					'country_id' => $customerCountry,
					'region_id' => $regionId, 
				);
			}
			if($addressData){
				$quote->getShippingAddress()->addData($addressData)->collectTotals();
			}else{
				$quote->getShippingAddress()->setData('same_as_billing', 1)->collectTotals();
			}
			$quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('freeshipping_freeshipping')->setPaymentMethod('cashondelivery');
			
			$quote->getPayment()->importData(array('method' => 'cashondelivery'));
			$quote->collectTotals()->save();
			$quote->setInventoryProcessed(true);
			$service = Mage::getModel('sales/service_quote', $quote);
			$service->submitAll();
			$order = $service->getOrder();
			$subTotal = $subTotalInclTax = $orderTotal = $taxTotal = $discountTotal = 0;
			$items = $order->getAllItems();
			foreach($items as $item) {
				$itemID = $item->getProduct()->getId();
				foreach($data['products'] as $product_info){
					if( $itemID == $product_info['product_id']){
						$priceInclTax = $unitPrice = $product_info['unitPrice'];
						$quantity = $product_info['quantity'];
						$taxAmount = $product_info['taxAmount'];
						$discountAmount = $product_info['discountAmount'];
						if($taxAmount > 0){
							$priceInclTax = $unitPrice + round($taxAmount/$quantity,3);
							$item->setTaxAmount($taxAmount);
							$item->setBaseTaxAmount($taxAmount);
							$taxTotal += $taxAmount;
						}
						if($product_info['taxRate'] > 0){
							$item->setTaxPercent($product_info['taxRate']);
						}
						if($discountAmount > 0){
							$item->setDiscountAmount($discountAmount)->setBaseDiscountAmount($discountAmount);
							$discountTotal += $discountAmount;
						}
						$item->setQtyOrdered($quantity)->setPrice($unitPrice)->setBasePrice($unitPrice)->setOriginalPrice($unitPrice)->setBaseOriginalPrice($unitPrice)->setPriceInclTax($priceInclTax)->setBasePriceInclTax($priceInclTax);
						$rowTotal = $unitPrice*$quantity;
						$subTotal += $rowTotal;
						$rowTotalInclTax = $priceInclTax*$quantity;
						$subTotalInclTax += $rowTotalInclTax;
						$item->setRowTotal($rowTotal)->setBaseRowTotal($rowTotal)->setRowTotalInclTax($rowTotalInclTax)->setBaseRowTotalInclTax($rowTotalInclTax);
						$item->save();
					}
				}
			}
			$timeStamp = $data['timeStamp'];
			$createdTime = date('Y-m-d H:i:s', strtotime($timeStamp));
			$orderTotal = $subTotalInclTax - $discountTotal;
			$paymentMethod = $this->getPaymentMethod($data['payments']);
			$order->setCreatedAt($createdTime)->setSubtotal($subTotal)->setBaseSubtotal($subTotal)->setSubtotalInclTax($subTotal)->setBaseSubtotalInclTax($subTotal)->setTaxAmount($taxTotal)->setBaseTaxAmount($taxTotal)->setDiscountAmount($discountTotal)->setBaseDiscountAmount($discountTotal)->setGrandTotal($orderTotal)->setBaseGrandTotal($orderTotal);
			$order->addStatusToHistory('complete', 'Payment method: '.$paymentMethod, false);
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
			$invoice->setCreatedAt($createdTime)->register()->save();
			$order->setData('state', "complete")->setStatus("complete")->save();
			return $order->getIncrementId();
		}catch(Exception $e){
			return 'Exception:'.$e->getMessage();
		}
	}
	
	public function getPaymentMethod($payments){
		
		$method = null;
		if(!empty($payments['salePaymentID'])){
			$method = $payments['PaymentType']['name'];
		}else{
			foreach($payments as $payment){
				if($payment['archived'] == 'false'){
					$method = $payments['PaymentType']['name'];
					break;
				}
			}
		}
		return $method;
	}
	
	public function convertToCountryCode($name = 'United States') {
	
		$countries = array(
				'AF' => 'Afghanistan',
				'AX' => 'Aland Islands',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua And Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia And Herzegovina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei Darussalam',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CD' => 'Congo, Democratic Republic',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'CI' => 'Cote D\'Ivoire',
				'HR' => 'Croatia',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands (Malvinas)',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GG' => 'Guernsey',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard Island & Mcdonald Islands',
				'VA' => 'Holy See (Vatican City State)',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran, Islamic Republic Of',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'IM' => 'Isle Of Man',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JE' => 'Jersey',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KR' => 'Korea',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Lao People\'s Democratic Republic',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libyan Arab Jamahiriya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macao',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia, Federated States Of',
				'MD' => 'Moldova',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PS' => 'Palestinian Territory, Occupied',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'BL' => 'Saint Barthelemy',
				'SH' => 'Saint Helena',
				'KN' => 'Saint Kitts And Nevis',
				'LC' => 'Saint Lucia',
				'MF' => 'Saint Martin',
				'PM' => 'Saint Pierre And Miquelon',
				'VC' => 'Saint Vincent And Grenadines',
				'WS' => 'Samoa',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome And Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia And Sandwich Isl.',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard And Jan Mayen',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania',
				'TH' => 'Thailand',
				'TL' => 'Timor-Leste',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad And Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks And Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom',
				'US' => 'United States',
				'UM' => 'United States Outlying Islands',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VE' => 'Venezuela',
				'VN' => 'Viet Nam',
				'VG' => 'Virgin Islands, British',
				'VI' => 'Virgin Islands, U.S.',
				'WF' => 'Wallis And Futuna',
				'EH' => 'Western Sahara',
				'YE' => 'Yemen',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe',
		);
		foreach ($countries as $code => $cName) {
			if (strtolower($cName) == trim(strtolower($name))) {
				return $code;
			}
		}
		return 'US';
	}
}
?>