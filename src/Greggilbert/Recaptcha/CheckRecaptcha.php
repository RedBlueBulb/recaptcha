<?php

namespace Greggilbert\Recaptcha;

/**
 * Handle sending out and receiving a response to validate the captcha
 */
class CheckRecaptcha
{
    const SERVER		= 'http://www.google.com/recaptcha/api';
    const SERVER_SECURE	= 'https://www.google.com/recaptcha/api';
    const ENDPOINT		= '/recaptcha/api/verify';
	const ENDPOINTV2	= '/recaptcha/api/siteverify';
    const VERIFY_SERVER	= 'www.google.com';
	
	/**
	 * Call out to reCAPTCHA and process the response
	 * @param string $challenge
	 * @param string $response
	 * @return array(bool, string)
	 */
	public function check($challenge, $response)
	{
		$parameters = http_build_query(array(
			'privatekey'	=> app('config')->get('recaptcha::private_key'),
			'remoteip'		=> app('request')->getClientIp(),
			'challenge'		=> $challenge,
			'response' => $response,
		));

		$http_request  = "GET" . self::ENDPOINT . "?" . $parameters . " HTTP/1.0\r\n";
		$http_request .= "Host: " . self::VERIFY_SERVER . "\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";

		$apiResponse = '';
		
		if (false == ($fs = @fsockopen(self::VERIFY_SERVER, 80)))
		{
			throw new \Exception('Could not open socket');
		}

		fwrite($fs, $http_request);

		while (!feof($fs))
		{
			$apiResponse .= fgets($fs, 1160); // One TCP-IP packet
		}
		
		fclose($fs);
		
		$apiResponse = explode("\r\n\r\n", $apiResponse, 2);

		return explode("\n", $apiResponse[1]);
	}

}
