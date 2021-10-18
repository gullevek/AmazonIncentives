<?php

namespace Amazon\AWS;

use Amazon\Client\Client;
use Amazon\Config\Config;
use Amazon\Exceptions\AmazonErrors;
use Amazon\Debug\AmazonDebug;
use Amazon\Response\CancelResponse;
use Amazon\Response\CreateBalanceResponse;
use Amazon\Response\CreateResponse;

class AWS
{
	public const SERVICE_NAME = 'AGCODService';
	public const ACCEPT_HEADER = 'accept';
	public const CONTENT_HEADER = 'content-type';
	public const HOST_HEADER = 'host';
	public const X_AMZ_DATE_HEADER = 'x-amz-date';
	public const X_AMZ_TARGET_HEADER = 'x-amz-target';
	public const AUTHORIZATION_HEADER = 'Authorization';
	public const AWS_SHA256_ALGORITHM = 'AWS4-HMAC-SHA256';
	public const KEY_QUALIFIER = 'AWS4';
	public const TERMINATION_STRING = 'aws4_request';
	public const CREATE_GIFT_CARD_SERVICE = 'CreateGiftCard';
	public const CANCEL_GIFT_CARD_SERVICE = 'CancelGiftCard';
	public const GET_AVAILABLE_FUNDS_SERVICE = 'GetAvailableFunds';

	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
		AmazonDebug::setFlag($config->getDebug());
		AmazonDebug::setId();
		AmazonDebug::writeLog([__METHOD__ => date('Y-m-d H:m:s.u')]);
	}

	/**
	 * @param float $amount
	 * @param string|null $creation_id
	 * @return CreateResponse
	 *
	 * @throws AmazonErrors
	 */
	public function getCode(float $amount, ?string $creation_id = null): CreateResponse
	{
		$service_operation = self::CREATE_GIFT_CARD_SERVICE;
		$payload = $this->getGiftCardPayload($amount, $creation_id);
		$canonical_request = $this->getCanonicalRequest($service_operation, $payload);
		$date_time_string = $this->getTimestamp();
		AmazonDebug::writeLog(['call' => __METHOD__]);
		$result = json_decode($this->makeRequest(
			$payload,
			$canonical_request,
			$service_operation,
			$date_time_string
		), true);
		return new CreateResponse($result);
	}

	/**
	 * @param string $creation_request_id
	 * @param string $gift_card_id
	 * @return CancelResponse
	 *
	 * @throws AmazonErrors
	 */
	public function cancelCode(string $creation_request_id, string $gift_card_id): CancelResponse
	{
		$service_operation = self::CANCEL_GIFT_CARD_SERVICE;
		$payload = $this->getCancelGiftCardPayload($creation_request_id, $gift_card_id);
		$canonical_request = $this->getCanonicalRequest($service_operation, $payload);
		$date_time_string = $this->getTimestamp();
		AmazonDebug::writeLog(['call' => __METHOD__]);
		$result = json_decode($this->makeRequest(
			$payload,
			$canonical_request,
			$service_operation,
			$date_time_string
		), true);
		return new CancelResponse($result);
	}

	/**
	 * @return CreateBalanceResponse
	 *
	 * @throws AmazonErrors
	 */
	public function getBalance(): CreateBalanceResponse
	{
		$service_operation = self::GET_AVAILABLE_FUNDS_SERVICE;
		$payload = $this->getAvailableFundsPayload();
		$canonical_request = $this->getCanonicalRequest($service_operation, $payload);
		$date_time_string = $this->getTimestamp();
		AmazonDebug::writeLog(['call' => __METHOD__]);
		$result = json_decode($this->makeRequest(
			$payload,
			$canonical_request,
			$service_operation,
			$date_time_string
		), true);
		return new CreateBalanceResponse($result);
	}

	/**
	 * @param string $payload
	 * @param string $canonical_request
	 * @param string $service_operation
	 * @param string $date_time_string
	 * @return string
	 */
	public function makeRequest(
		string $payload,
		string $canonical_request,
		string $service_operation,
		string $date_time_string
	): string {
		// debug
		AmazonDebug::writeLog([__METHOD__ => [
			'Operation' => $service_operation,
			'Payload' => $payload,
			'Cannonical Request' => $canonical_request,
			'Date Time String' => $date_time_string

		]]);
		$KEY_QUALIFIER = self::KEY_QUALIFIER;
		$canonical_request_hash = $this->buildHash($canonical_request);
		$string_to_sign = $this->buildStringToSign($canonical_request_hash);
		$authorization_value = $this->buildAuthSignature($string_to_sign);

		$secret_key = $this->config->getSecret();
		$endpoint = $this->config->getEndpoint();
		$region_name = $this->getRegion();

		$SERVICE_NAME = 'AGCODService';
		$service_target = 'com.amazonaws.agcod.' . $SERVICE_NAME . '.' . $service_operation;
		$date_string = $this->getDateString();

		$signature_aws_key = $KEY_QUALIFIER . $secret_key;

		$k_date = $this->hmac($date_string, $signature_aws_key);
		$k_date_hexis = $this->hmac($date_string, $signature_aws_key, false);
		$k_region = $this->hmac($region_name, $k_date);
		$k_region_hexis = $this->hmac($region_name, $k_date, false);
		$k_service_hexis = $this->hmac($SERVICE_NAME, $k_region, false);

		AmazonDebug::writeLog([__METHOD__ => [
			'Date' => $k_date_hexis,
			'Region' => $k_region_hexis,
			'Service' => $k_service_hexis,
		]]);

		$url = 'https://' . $endpoint . '/' . $service_operation;
		$headers = $this->buildHeaders($payload, $authorization_value, $date_time_string, $service_target);
		return (new Client())->request($url, $headers, $payload);
	}

	/**
	 * @param string $payload
	 * @param string $authorization_value
	 * @param string $date_time_string
	 * @param string $service_target
	 * @return array
	 */
	public function buildHeaders(
		string $payload,
		string $authorization_value,
		string $date_time_string,
		string $service_target
	): array {
		$ACCEPT_HEADER = self::ACCEPT_HEADER;
		$X_AMZ_DATE_HEADER = self::X_AMZ_DATE_HEADER;
		$X_AMZ_TARGET_HEADER = self::X_AMZ_TARGET_HEADER;
		$AUTHORIZATION_HEADER = self::AUTHORIZATION_HEADER;
		return [
			'Content-Type:' . $this->getContentType(),
			'Content-Length: ' . strlen($payload),
			$AUTHORIZATION_HEADER . ':' . $authorization_value,
			$X_AMZ_DATE_HEADER . ':' . $date_time_string,
			$X_AMZ_TARGET_HEADER . ':' . $service_target,
			$ACCEPT_HEADER . ':' . $this->getContentType()
		];
	}

	/**
	 * @param string $string_to_sign
	 * @return string
	 */
	public function buildAuthSignature(string $string_to_sign): string
	{
		$AWS_SHA256_ALGORITHM = self::AWS_SHA256_ALGORITHM;
		$SERVICE_NAME = self::SERVICE_NAME;
		$TERMINATION_STRING = self::TERMINATION_STRING;
		$ACCEPT_HEADER = self::ACCEPT_HEADER;
		$HOST_HEADER = self::HOST_HEADER;
		$X_AMZ_DATE_HEADER = self::X_AMZ_DATE_HEADER;
		$X_AMZ_TARGET_HEADER = self::X_AMZ_TARGET_HEADER;

		$aws_key_id = $this->config->getAccessKey();
		$region_name = $this->getRegion();

		$date_string = $this->getDateString();
		$derived_key = $this->buildDerivedKey();
		// Calculate signature per http://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
		$final_signature = $this->hmac($string_to_sign, $derived_key, false);

		// Assemble Authorization Header with signing information
		// per http://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html
		$authorization_value =
			$AWS_SHA256_ALGORITHM
			. ' Credential=' . $aws_key_id
			. '/' . $date_string . '/' . $region_name . '/' . $SERVICE_NAME . '/' . $TERMINATION_STRING . ','
			. ' SignedHeaders='
			. $ACCEPT_HEADER . ';' . $HOST_HEADER . ';' . $X_AMZ_DATE_HEADER . ';' . $X_AMZ_TARGET_HEADER . ','
			. ' Signature='
			. $final_signature;

		return $authorization_value;
	}

	/**
	 * @param string $canonical_request_hash
	 * @return string
	 */
	public function buildStringToSign($canonical_request_hash): string
	{
		$AWS_SHA256_ALGORITHM = self::AWS_SHA256_ALGORITHM;
		$TERMINATION_STRING = self::TERMINATION_STRING;
		$SERVICE_NAME = self::SERVICE_NAME;
		$region_name = $this->getRegion();
		$date_time_string = $this->getTimestamp();
		$date_string = $this->getDateString();
		$string_to_sign = "$AWS_SHA256_ALGORITHM\n"
			. "$date_time_string\n"
			. "$date_string/$region_name/$SERVICE_NAME/$TERMINATION_STRING\n"
			. "$canonical_request_hash";

		return $string_to_sign;
	}

	/**
	 * @param bool $rawOutput
	 * @return string
	 */
	public function buildDerivedKey(bool $rawOutput = true): string
	{
		$KEY_QUALIFIER = self::KEY_QUALIFIER;
		$TERMINATION_STRING = self::TERMINATION_STRING;
		$SERVICE_NAME = self::SERVICE_NAME;

		$aws_secret_key = $this->config->getSecret();
		// Append Key Qualifier, "AWS4", to secret key per
		// shttp://docs.aws.amazon.com/general/latest/gr/signature-v4-examples.html
		$signature_aws_key = $KEY_QUALIFIER . $aws_secret_key;
		$region_name = $this->getRegion();
		$date_string = $this->getDateString();

		$k_date = $this->hmac($date_string, $signature_aws_key);
		$k_region = $this->hmac($region_name, $k_date);
		$k_service = $this->hmac($SERVICE_NAME, $k_region);

		// Derived the Signing key (derivedKey aka kSigning)
		return $this->hmac($TERMINATION_STRING, $k_service, $rawOutput);
	}

	/**
	 * @return string
	 */
	public function getRegion(): string
	{
		$endpoint = $this->config->getEndpoint();
		// default region
		$region_name = 'us-east-1';

		switch ($endpoint) {
			case 'agcod-v2.amazon.com':
			case 'agcod-v2-gamma.amazon.com':
				$region_name = 'us-east-1';
				break;
			case 'agcod-v2-eu.amazon.com':
			case 'agcod-v2-eu-gamma.amazon.com':
				$region_name = 'us-west-1';
				break;
			case 'agcod-v2-fe.amazon.com':
			case 'agcod-v2-fe-gamma.amazon.com':
				$region_name = 'us-west-2';
				break;
		}
		return $region_name;
	}


	/**
	 * @param float $amount
	 * @param string $creation_id
	 * @return string
	 */
	public function getGiftCardPayload(float $amount, ?string $creation_id = null): string
	{
		$amount = trim($amount);
		$payload = [
			'creationRequestId' => $creation_id ?: uniqid($this->config->getPartner() . '_'),
			'partnerId' => $this->config->getPartner(),
			'value' =>
				[
					'currencyCode' => $this->config->getCurrency(),
					'amount' => (float)$amount
				]
		];
		return json_encode($payload);
	}

	/**
	 * @param string $creation_request_id
	 * @param string $gift_card_id
	 * @return string
	 */
	public function getCancelGiftCardPayload(string $creation_request_id, string $gift_card_id): string
	{
		$gift_card_response_id = trim($gift_card_id);
		$payload = [
			'creationRequestId' => $creation_request_id,
			'partnerId' => $this->config->getPartner(),
			'gcId' => $gift_card_response_id
		];
		return json_encode($payload);
	}

	/**
	 * @return string
	 */
	public function getAvailableFundsPayload(): string
	{
		$payload = [
			'partnerId' => $this->config->getPartner(),
		];
		return json_encode($payload);
	}

	/**
	 * @param string $service_operation
	 * @param string $payload
	 * @return string
	 */
	public function getCanonicalRequest(string $service_operation, string $payload): string
	{
		$HOST_HEADER = self::HOST_HEADER;
		$X_AMZ_DATE_HEADER = self::X_AMZ_DATE_HEADER;
		$X_AMZ_TARGET_HEADER = self::X_AMZ_TARGET_HEADER;
		$ACCEPT_HEADER = self::ACCEPT_HEADER;
		$payload_hash = $this->buildHash($payload);
		$canonical_headers = $this->buildCanonicalHeaders($service_operation);
		$canonical_request = "POST\n"
			. "/$service_operation\n\n"
			. "$canonical_headers\n\n"
			. "$ACCEPT_HEADER;$HOST_HEADER;$X_AMZ_DATE_HEADER;$X_AMZ_TARGET_HEADER\n"
			. "$payload_hash";
		return $canonical_request;
	}

	/**
	 * @param string $data
	 * @return string
	 */
	public function buildHash(string $data): string
	{
		return hash('sha256', $data);
	}

	/**
	 * @return false|string
	 */
	public function getTimestamp()
	{
		return gmdate('Ymd\THis\Z');
	}

	/**
	 * @param string $data
	 * @param string $key
	 * @param bool $raw
	 * @return string
	 */
	public function hmac(string $data, string $key, bool $raw = true): string
	{
		return hash_hmac('sha256', $data, $key, $raw);
	}

	/**
	 * @return bool|string
	 */
	public function getDateString()
	{
		return substr($this->getTimestamp(), 0, 8);
	}

	/**
	 * @return string
	 */
	public function getContentType(): string
	{
		return 'application/json';
	}

	/**
	 * @param string $service_operation
	 * @return string
	 */
	public function buildCanonicalHeaders(string $service_operation): string
	{
		$ACCEPT_HEADER = self::ACCEPT_HEADER;
		$HOST_HEADER = self::HOST_HEADER;
		$X_AMZ_DATE_HEADER = self::X_AMZ_DATE_HEADER;
		$X_AMZ_TARGET_HEADER = self::X_AMZ_TARGET_HEADER;
		$date_time_string = $this->getTimestamp();
		$endpoint = $this->config->getEndpoint();
		$content_type = $this->getContentType();
		return "$ACCEPT_HEADER:$content_type\n"
			. "$HOST_HEADER:$endpoint\n"
			. "$X_AMZ_DATE_HEADER:$date_time_string\n"
			. "$X_AMZ_TARGET_HEADER:com.amazonaws.agcod.AGCODService.$service_operation";
	}
}

// __END__
