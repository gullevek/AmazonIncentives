<?php

namespace Amazon\Client;

use Amazon\Exceptions\AmazonErrors;
use Amazon\Debug\AmazonDebug;

class Client implements ClientInterface
{
	// instead of JsonResponse::HTTP_OK
	private const HTTP_OK = 200;

	/**
	 *
	 * @param string $url The URL being requested, including domain and protocol
	 * @param array $headers Headers to be used in the request
	 * @param array|string $params Can be nested for arrays and hashes
	 *
	 *
	 * @return String
	 */
	public function request(string $url, array $headers, $params): string
	{
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		// curl_setopt($handle, CURLOPT_FAILONERROR, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($handle);

		if ($result === false) {
			$err = curl_errno($handle);
			$message = curl_error($handle);
			$this->handleCurlError($url, $err, $message);
		}

		if (curl_getinfo($handle, CURLINFO_HTTP_CODE) !== self::HTTP_OK) {
			$err = curl_errno($handle);
			AmazonDebug::writeLog(['CURL_REQUEST_RESULT' => $result]);
			// extract all the error codes from Amazon
			$result_ar = json_decode($result, true);
			$error_status = $result_ar['agcodResponse']['status'] ?? 'FAILURE';
			$error_code = $result_ar['errorCode'] ?? 'E999';
			$error_type = $result_ar['errorType'] ?? 'OtherUnknownError';
			$message = $result_ar['message'] ?? 'Unknown error occured';
			throw AmazonErrors::getError(
				$error_status,
				$error_code,
				$error_type,
				$message,
				$err
			);
		}
		return $result;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $url
	 * @param string $errno
	 * @param string $message
	 * @return void
	 */
	private function handleCurlError(string $url, int $errno, string $message): void
	{
		switch ($errno) {
			case CURLE_COULDNT_CONNECT:
			case CURLE_COULDNT_RESOLVE_HOST:
			case CURLE_OPERATION_TIMEOUTED:
				$msg = 'Could not connect to AWS (' . $url . ').  Please check your '
					. 'internet connection and try again.';
				break;
			case CURLE_SSL_CACERT:
			case CURLE_SSL_PEER_CERTIFICATE:
				$msg = 'Could not verify AWS SSL certificate.  Please make sure '
					. 'that your network is not intercepting certificates.  '
					. '(Try going to ' . $url . 'in your browser.)  '
					. 'If this problem persists,';
				break;
			case 0:
			default:
				$msg = 'Unexpected error communicating with AWS. ' . $message;
		}

		throw new \RuntimeException($msg);
	}
}

// __END__
