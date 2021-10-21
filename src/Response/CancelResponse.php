<?php

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CancelResponse
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
	 * Amazon Gift Card status
	 *
	 * @var string
	 */
	protected $status;
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
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getRawJson(): string
	{
		return (json_encode($this->raw_json)) ?: '';
	}

	/**
	 * @param  array<mixed> $json_response
	 * @return CancelResponse
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
		// SUCCESS, FAILURE, RESEND
		if (array_key_exists('status', $json_response)) {
			$this->status = $json_response['status'];
		}

		return $this;
	}
}

// __END__
