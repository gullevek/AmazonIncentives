<?php

/*
 * Amazon Incentive Code
 *
 * # settings:
 * aws endpoint (also sets region)
 * aws key
 * aws secret
 * aws partner id
 * money type (set as default, override in call)
 * money value (set as default, override in call)
 *
 * # checks
 * endpoint + region must match
 *
 * # calls:
 * create gift card: CreateGiftCard
 * cancel gift card: CancelGiftCard
 *
 * activate gift card: ActivateGiftCard
 * deactivate gift card: DeactivateGiftCard
 *
 * gift card status: ActivationStatusCheck
 *
 * check available funds: GetAvailablefunds
 *
 * api server health check
 *
 * # sub classes
 * config reader/checker
 * API v4 encrypter
 * submitter/data getter
 * error handler/retry
 */

namespace Amazon;

use Amazon\AWS\AWS;
use Amazon\Config\Config;
use Amazon\Exceptions\AmazonErrors;

class AmazonIncentives
{
	private $config;

	/**
	 * AmazonGiftCode constructor.
	 *
	 * @param string|null $key
	 * @param string|null $secret
	 * @param string|null $partner
	 * @param string|null $endpoint
	 * @param string|null $currency
	 * @param bool|null $debug
	 */
	public function __construct(
		string $key = null,
		string $secret = null,
		string $partner = null,
		string $endpoint = null,
		string $currency = null,
		bool $debug = null
	) {
		// load AWS settings
		// fail here if settings missing
		$this->config = new Config($key, $secret, $partner, $endpoint, $currency, $debug);
	}

	// *********************************************************************
	// PRIVATE HELPER METHODS
	// *********************************************************************

	// *********************************************************************
	// PUBLIC DEBUG METHODS
	// *********************************************************************

	// like log collector (array) and returner

	// *********************************************************************
	// PUBLIC METHODS
	// *********************************************************************

	/**
	 * @param float $value
	 * @param string $creation_request_id AWS creationRequestId
	 * @return                            Response\CreateResponse
	 *
	 * @throws AmazonErrors
	 */
	public function buyGiftCard(float $value, string $creation_request_id = null): Response\CreateResponse
	{
		return (new AWS($this->config))->getCode($value, $creation_request_id);
	}


	/**
	 * @param string $creation_request_id AWS creationRequestId
	 * @param string $gift_card_id        AWS gcId
	 * @return Response\CancelResponse
	 */
	public function cancelGiftCard(string $creation_request_id, string $gift_card_id): Response\CancelResponse
	{
		return (new AWS($this->config))->cancelCode($creation_request_id, $gift_card_id);
	}

	/**
	 * @return Response\CreateBalanceResponse
	 *
	 * @throws AmazonErrors
	 */
	public function getAvailableFunds(): Response\CreateBalanceResponse
	{
		return (new AWS($this->config))->getBalance();
	}

	/**
	 * AmazonGiftCode make own client.
	 *
	 * @param string|null $key
	 * @param string|null $secret
	 * @param string|null $partner
	 * @param string|null $endpoint
	 * @param string|null $currency
	 * @param bool|null $debug
	 * @return AmazonGiftCode
	 */
	public static function make(
		string $key = null,
		string $secret = null,
		string $partner = null,
		string $endpoint = null,
		string $currency = null,
		bool $debug = null
	): AmazonIncentives {
		return new static($key, $secret, $partner, $endpoint, $currency, $debug);
	}

	/**
	 * Decodes the Exception message body
	 * Returns an array with code (Amazon error codes), type (Amazon error info)
	 * message (Amazon returned error message string)
	 *
	 * @param  string $message Exception message json string
	 * @return array           Decoded with code, type, message fields
	 */
	public static function decodeExceptionMessage(string $message): array
	{
		$message_ar = json_decode($message, true);
		// if we have an error, build empty block and only fill message
		if (json_last_error()) {
			$message_ar = [
				'status' => '',
				'code' => '',
				'type' => '',
				'message' => $message,
				'log_id' => '',
				'log' => []
			];
		}
		return $message_ar;
	}

	// *********************************************************************
	// PUBLIC TEST METHODS
	// *********************************************************************

	public function checkMe(): array
	{
		$data = [];

		$data['ENV'] = $_ENV;
		$data['CONFIG'] = $this->config;
		$data['KEY'] = $this->config->getAccessKey();

		return $data;
	}
}

// __END__
