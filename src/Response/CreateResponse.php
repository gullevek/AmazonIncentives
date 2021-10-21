<?php

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CreateResponse
{
	/**
	 * Amazon Gift Card gcId.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Amazon Gift Card creationRequestId
	 *
	 * @var string
	 */
	protected $creation_request_id;

	/**
	 * Amazon Gift Card gcClaimCode
	 *
	 * @var string
	 */
	protected $claim_code;

	/**
	 * Amazon Gift Card amount
	 *
	 * @var float
	 */
	protected $value;

	/**
	 * Amazon Gift Card currency
	 *
	 * @var string
	 */
	protected $currency;
	/**
	 * Amazon Gift Card status
	 *
	 * @var string
	 */
	protected $status;
	/**
	 * Amazon Gift Card Expiration Date
	 *
	 * @var string
	 */
	protected $expiration_date;
	/**
	 * Amazon Gift Card Expiration Date
	 *
	 * @var string
	 */
	protected $card_status;
	/**
	 * Amazon Gift Card Raw JSON
	 *
	 * @var array<mixed>
	 */
	protected $raw_json;

	/**
	 * Response constructor.
	 * @param array<mixed> $json_response
	 */
	public function __construct(array $json_response)
	{
		$this->raw_json = $json_response;
		$this->parseJsonResponse($json_response);
	}

	/**
	 * @return array<mixed>
	 */
	public function getLog(): array
	{
		return AmazonDebug::getLog(AmazonDebug::getId());
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getCreationRequestId(): string
	{
		return $this->creation_request_id;
	}

	/**
	 * @return string
	 */
	public function getClaimCode(): string
	{
		return $this->claim_code;
	}

	/**
	 * @return float
	 */
	public function getValue(): float
	{
		return $this->value;
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
	public function getExpirationDate(): string
	{
		return $this->expiration_date;
	}

	/**
	 * @return string
	 */
	public function getCardStatus(): string
	{
		return $this->card_status;
	}


	/**
	 * @return string
	 */
	public function getRawJson(): string
	{
		return (json_encode($this->raw_json)) ?: '';
	}

	/**
	 * @param array<mixed> $json_response
	 * @return CreateResponse
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
		if (array_key_exists('gcId', $json_response)) {
			$this->id = $json_response['gcId'];
		}
		if (array_key_exists('creationRequestId', $json_response)) {
			$this->creation_request_id = $json_response['creationRequestId'];
		}
		if (array_key_exists('gcClaimCode', $json_response)) {
			$this->claim_code = $json_response['gcClaimCode'];
		}
		if (array_key_exists('amount', $json_response['cardInfo']['value'])) {
			$this->value = $json_response['cardInfo']['value']['amount'];
		}
		if (array_key_exists('currencyCode', $json_response['cardInfo']['value'])) {
			$this->currency = $json_response['cardInfo']['value']['currencyCode'];
		}
		if (array_key_exists('gcExpirationDate', $json_response)) {
			$this->expiration_date = $json_response['gcExpirationDate'];
		}
		if (array_key_exists('cardStatus', $json_response['cardInfo'])) {
			$this->card_status = $json_response['cardInfo']['cardStatus'];
		}
		// SUCCESS, FAILURE, RESEND
		if (array_key_exists('status', $json_response)) {
			$this->status = $json_response['status'];
		}

		return $this;
	}
}

// __END__
