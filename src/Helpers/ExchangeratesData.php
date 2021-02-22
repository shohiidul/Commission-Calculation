<?php 

namespace App\Helpers;

class ExchangeratesData
{

	private $curl = "https://api.exchangeratesapi.io/latest";

	/**
	 * [getCurlData sends curl request]
	 * @return [$resultdata] [response from request]
	 */
	private function getCurlData( $curl ) : array
	{

	    $url = curl_init( $curl );
	    curl_setopt($url,CURLOPT_HTTPHEADER, array('Content-Type:application/json') );
		curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($url,CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($url,CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($url,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($url,CURLOPT_CONNECTTIMEOUT, 10);		
		curl_setopt($url,CURLOPT_TIMEOUT, 20);		 

		$result = curl_exec($url);		

		if (curl_errno($url)) {
		    $error_msg = curl_error($url);
		}
		curl_close($url);

		$resultdata = array();
		if (isset($error_msg)) {
			$resultdata['flag'] = false;
			$resultdata['msg']  = $error_msg;	    
		}	
		else {
			$resultdata['flag'] = true;
			$resultdata['msg']  = 'success';
			$resultdata['data'] = json_decode($result, true);
		}

		return $resultdata;
	}

	public function getRates() : array
	{
		$rates = $this->getCurlData( $this->curl );


		return $rates;
	}

}
