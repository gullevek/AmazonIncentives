<?php

declare(strict_types=1);

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CancelResponse
{
    /** @var string Amazon Gift Card gcId (gift card id). */
    protected $id = '';
    /** @var string Amazon Gift Card creationRequestId (creation request id) */
    protected $creation_request_id = '';
    /** @var string Amazon Gift Card status */
    protected $status = '';
    /** @var array<mixed> Amazon Gift Card Raw JSON */
    protected $raw_json = [];

    /**
     * Response constructor for canceling gitf cards
     *
     * @param array<mixed> $json_response JSON response from web request to AWS
     */
    public function __construct(array $json_response)
    {
        $this->raw_json = $json_response;
        $this->parseJsonResponse($json_response);
    }

    /**
     * Get log entry with current set log id
     *
     * @return array<mixed> Log array
     */
    public function getLog(): array
    {
        return AmazonDebug::getLog(AmazonDebug::getId());
    }

    /**
     * The gift card id as created by the previous get code call
     *
     * @return string Gift card id
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Creation Request id from original get code call
     *
     * @return string Creation request id
     */
    public function getCreationRequestId(): string
    {
        return $this->creation_request_id;
    }

    /**
     * Request status
     *
     * @return string Request status as string: SUCCESS, FAILURE, RESEND
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns the request data as json string. This is a re-encode from decoded
     * makeRequest call
     *
     * @return string JSON encoded string from the return values
     */
    public function getRawJson(): string
    {
        return (json_encode($this->raw_json)) ?: '';
    }

    /**
     * Set class variables with response data from makeRequest and return self
     *
     * @param  array<mixed>   $json_response JSON response as array
     * @return CancelResponse                Return self object
     */
    public function parseJsonResponse(array $json_response): self
    {
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
