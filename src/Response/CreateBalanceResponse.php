<?php

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CreateBalanceResponse
{
	/**
	 * Amazon Gift Card Balance Amount
	 *
	 * @var string
	 */
	protected $amount;
	/**
	 * Amazon Gift Card Balance Currency
	 *
	 * @var string
	 */
	protected $currency;
	/**
	 * Amazon Gift Card Balance Status
	 *
	 * @var string
	 */
	protected $status;
	/**
	 * Amazon Gift Card Balance Timestamp
	 *
	 * @var string
	 */
	protected $timestamp;
	/**
	 * Amazon Gift Card Raw JSON
	 *
	 * @var string
	 */
	protected $raw_json;
	/**
	 * @var array
	 */
	protected $log;

	/**
	 * Response constructor.
	 *
	 * @param array $json_response
	 */
	public function __construct(array $json_response)
	{
		$this->raw_json = $json_response;
		$this->log = AmazonDebug::getLog(AmazonDebug::getId());
		$this->parseJsonResponse($json_response);
	}

	/**
	 * @return array
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * @return string
	 */
	public function getAmount(): string
	{
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getTimestamp(): string
	{
		return $this->timestamp;
	}

	/**
	 * @return string
	 */
	public function getRawJson(): string
	{
		return json_encode($this->raw_json);
	}

	/**
	 * Undocumented function
	 *
	 * @param array $json_response
	 * @return CreateBalanceResponse
	 */
	public function parseJsonResponse(array $json_response): self
	{
		if (!is_array($json_response)) {
			throw AmazonErrors::getError(
				'FAILURE',
				'E001',
				'NonScalarValue',
				'Response must be a scalar value',
				0
			);
		}
		if (array_key_exists('amount', $json_response['availableFunds'])) {
			$this->amount = $json_response['availableFunds']['amount'];
		}
		if (array_key_exists('currencyCode', $json_response['availableFunds'])) {
			$this->currency = $json_response['availableFunds']['currencyCode'];
		}
		// SUCCESS, FAILURE, RESEND
		if (array_key_exists('status', $json_response)) {
			$this->status = $json_response['status'];
		}
		if (array_key_exists('timestamp', $json_response)) {
			$this->timestamp = $json_response['timestamp'];
		}

		return $this;
	}
}
