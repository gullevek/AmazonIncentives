<?php

declare(strict_types=1);

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CreateBalanceResponse
{
    /** @var float Amazon Gift Card Balance Amount */
    protected $amount = 0;
    /** @var string Amazon Gift Card Balance Currency */
    protected $currency = '';
    /** @var string Amazon Gift Card Balance Status */
    protected $status = '';
    /** @var string Amazon Gift Card Balance Timestamp */
    protected $timestamp = '';
    /** @var array<mixed> Amazon Gift Card Raw JSON */
    protected $raw_json = [];

    /**
     * Response constructor for requesting account funds status
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
     * Return the current available funds amount
     *
     * @return float Funds amount in set currency
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the set currency type
     *
     * @return string Currency type. Eg USD, JPY, etc
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get timestamp as set.
     * eg 20220609T061446Z
     *
     * @return string Timestamp string. Ymd\THis\Z
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
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
     * @param  array<mixed>          $json_response JSON response as array
     * @return CreateBalanceResponse                Return self object
     */
    public function parseJsonResponse(array $json_response): self
    {
        if (
            is_array($json_response['availableFunds']) &&
            array_key_exists('amount', $json_response['availableFunds']) &&
            is_numeric($json_response['availableFunds']['amount'])
        ) {
            $this->amount = (float)$json_response['availableFunds']['amount'];
        }
        if (
            is_array($json_response['availableFunds']) &&
            array_key_exists('currencyCode', $json_response['availableFunds']) &&
            is_string($json_response['availableFunds']['currencyCode'])
        ) {
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
