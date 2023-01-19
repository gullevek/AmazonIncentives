<?php

namespace gullevek\AmazonIncentives\Response;

use gullevek\AmazonIncentives\Debug\AmazonDebug;

class CreateResponse
{
    /** @var string Amazon Gift Card gcId */
    protected $id = '';
    /** @var string Amazon Gift Card creationRequestId */
    protected $creation_request_id = '';
    /** @var string Amazon Gift Card gcClaimCode */
    protected $claim_code = '';
    /** @var float Amazon Gift Card amount */
    protected $value = 0;
    /** @var string Amazon Gift Card currency */
    protected $currency = '';
    /** @var string Amazon Gift Card status */
    protected $status = '';
    /** @var string Amazon Gift Card Expiration Date */
    protected $expiration_date = '';
    /** @var string Amazon Gift Card Expiration Date */
    protected $card_status = '';
    /** @var array<mixed> Amazon Gift Card Raw JSON as array */
    protected $raw_json = [];

    /**
     * Response constructor for creating gift cards
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
     * Gift Card ID returned from AWS. Can be used in the cancel request
     *
     * @return string Gift card id
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Either the one set with the method parameter, or automatically created
     * during get code request
     *
     * @return string Creation request id
     */
    public function getCreationRequestId(): string
    {
        return $this->creation_request_id;
    }

    /**
     * The actual gift code, recommended not to be stored anywhere and only shown
     * to user
     *
     * @return string Gift order claim code on AWS
     */
    public function getClaimCode(): string
    {
        return $this->claim_code;
    }

    /**
     * The ordered gift code value in given currency
     *
     * @return float Gift order value in currency
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * The currently set currency
     *
     * @return string Currency type. Eg USD, JPY, etc
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Expiration date for the ordered gift code.
     * eg 20220609T061446Z
     *
     * @return string Timestamp until when the gift code is valid. Ymd\THis\Z
     */
    public function getExpirationDate(): string
    {
        return $this->expiration_date;
    }

    /**
     * Gift card status. If the same creation request id is sent again and the
     * gift card got cancled, this is reflected here
     *
     * @return string Gift card status
     */
    public function getCardStatus(): string
    {
        return $this->card_status;
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
     * @Returns the request data as json string. This is a re-encode from decoded
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
     * @return CreateResponse                Return self object
     */
    public function parseJsonResponse(array $json_response): self
    {
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
