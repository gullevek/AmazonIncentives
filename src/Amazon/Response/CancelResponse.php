<?php

namespace Amazon\Response;

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
	 * @var string
	 */
	protected $raw_json;
	/**
	 * @var array
	 */
	protected $log;

	/**
	 * Response constructor.
	 * @param array $json_response
	 */
	public function __construct(array $json_response)
	{
		$this->raw_json = $json_response;
		$this->log = \Amazon\Debug\AmazonDebug::getLog(\Amazon\Debug\AmazonDebug::getId());
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
		return json_encode($this->raw_json);
	}

	/**
	 * @param  array $json_response
	 * @return CancelResponse
	 */
	public function parseJsonResponse(array $json_response): self
	{
		if (!is_array($json_response)) {
			throw new \RuntimeException('Response must be a scalar value');
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
