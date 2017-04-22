<?php

class Magegiant_GiantPointsRefer_Block_Refer_Gmail extends Magegiant_GiantPointsRefer_Block_Refer_Abstract {

	public function getContacts() {
		$client_id = Mage::getStoreConfig('giantpoints/referral_system_configuration/google_consumer_key');
		$client_secret = Mage::getStoreConfig('giantpoints/referral_system_configuration/google_consumer_secret');
		$redirect_uri = Mage::getUrl("*/*/gmail", array('_nosid' => true,'_secure'=>false));
		$max_results = 10000;

		$auth_code = $this->getRequest()->getParam("code");

		/* get token */
		$post = 'code='.$auth_code.'&client_id='.$client_id.'&client_secret='.$client_secret.'&redirect_uri='.urlencode($redirect_uri).'&grant_type=authorization_code';
		$accesstokenResult = $this->curl_connect('https://accounts.google.com/o/oauth2/token', $post);
		$response = json_decode($accesstokenResult);
		$accesstoken = $response->access_token;

		/* get contact */
		$url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $max_results . '&oauth_token=' . $accesstoken;
		$xmlresponse = $this->curl_connect($url);

		/* to html */
		if ((strlen(stristr($xmlresponse, 'Authorization required')) > 0) && (strlen(stristr($xmlresponse, 'Error ')) > 0)) {
			echo "<h2>OOPS !! Something went wrong. Please try reloading the page.</h2>";
			exit();
		}
		echo "<h3>Email Addresses:</h3>";
		$xml = new SimpleXMLElement($xmlresponse);
		$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

		$list = array();
		foreach ($xml->entry as $entry) {
			$_contact = array();
			$_contact['name']= (string)$entry->title;
			foreach ($entry->xpath('gd:email') as $email) {
				$_contact['email']= (string)$email->attributes()->address;
			}
			if (isset($_contact['email']) && $_contact['email'])
				$list[] = $_contact;
		}
		return $list;
	}

	public function curl_connect($url, $post = null) {
		$curl = curl_init();
		$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

		curl_setopt($curl, CURLOPT_URL, $url); //The URL to fetch. This can also be set when initializing a session with curl_init().
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); //The number of seconds to wait while trying to connect.
		if($post){
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent); //The contents of the "User-Agent: " header to be used in a HTTP request.
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); //To follow any "Location: " header that the server sends as part of the HTTP header.
		curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //To stop cURL from verifying the peer's certificate.
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		$contents = curl_exec($curl);
		curl_close($curl);
		return $contents;
	}
}
