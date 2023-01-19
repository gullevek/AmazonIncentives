<?php

namespace gullevek\AmazonIncentives\AWS;

use gullevek\AmazonIncentives\Client\Client;
use gullevek\AmazonIncentives\Config\Config;
use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;
use gullevek\AmazonIncentives\Response\CancelResponse;
use gullevek\AmazonIncentives\Response\CreateBalanceResponse;
use gullevek\AmazonIncentives\Response\CreateResponse;

class AWS
{
    /** @var string What AWS Service to use: Gift Card on Demand (GCOD) */
    public const SERVICE_NAME = 'AGCODService';
    /** @var string */
    public const ACCEPT_HEADER = 'accept';
    /** @var string content-type */
    public const CONTENT_HEADER = 'content-type';
    /** @var string */
    public const HOST_HEADER = 'host';
    /** @var string */
    public const X_AMZ_DATE_HEADER = 'x-amz-date';
    /** @var string */
    public const X_AMZ_TARGET_HEADER = 'x-amz-target';
    /** @var string */
    public const AUTHORIZATION_HEADER = 'Authorization';
    /** @var string type of encryption type */
    public const AWS_SHA256_ALGORITHM = 'AWS4-HMAC-SHA256';
    /** @var string key type to use */
    public const KEY_QUALIFIER = 'AWS4';
    /** @var string */
    public const TERMINATION_STRING = 'aws4_request';
    /** @var string Service to use: Create Gift Card */
    public const CREATE_GIFT_CARD_SERVICE = 'CreateGiftCard';
    /** @var string Service to use: Cancle Gift Card */
    public const CANCEL_GIFT_CARD_SERVICE = 'CancelGiftCard';
    /** @var string Service to use: Get Available Funds */
    public const GET_AVAILABLE_FUNDS_SERVICE = 'GetAvailableFunds';

    /** @var Config Configuration class with all settings */
    private $config;

    /**
     * Initialize the main AWS class. This class prepares and sends all the actions
     * and returns reponses as defined in in the CreateResponse class
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        AmazonDebug::writeLog([__METHOD__ => date('Y-m-d H:m:s.u')]);
    }

    /**
     * Bug a gift card
     *
     * @param  float          $amount      Amount to buy a gifr card in set currencty
     * @param  string|null    $creation_id Override creation id, if not set will
     *                                     be created automatically. If not valid error
     *                                     will be thrown
     * @return CreateResponse              Object with AWS response data
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
     * Cancle an ordered gift card, only possible within the the time limit
     *
     * @param  string         $creation_request_id Previously created creation request id
     * @param  string         $gift_card_id        Previously created gift card id
     * @return CancelResponse                      Object with AWS response data
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
     * Get current account funds
     *
     * @return CreateBalanceResponse Object with AWS response data
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
     * return a new curl connection client class
     *
     * @return Client Curl connection Client
     */
    public function newClient(): Client
    {
        return new Client();
    }

    /**
     * General request method for all actions
     * Calls the Client class that actually runs the json request
     * For service_operation valid data see AWS GCOD documentation
     *
     * @param  string $payload           The data needed for this request
     * @param  string $canonical_request Header data to send for this request
     * @param  string $service_operation Service operation. CREATE_GIFT_CARD_SERVICE,
     *                                   CANCEL_GIFT_CARD_SERVICE or
     *                                   GET_AVAILABLE_FUNDS_SERVICE constant values
     * @param  string $date_time_string  Ymd\THis\Z encoded timestamp, getTimestamp()
     * @return string                    Request result as string, json data
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
        $headers = $this->buildHeaders(
            $payload,
            $authorization_value,
            $date_time_string,
            $service_target
        );
        return ($this->newClient())->request($url, $headers, $payload);
    }

    /**
     * Build the headers used in the makeRequest method.
     * These are the HTML headers used with curl
     *
     * @param  string       $payload             Paylout to create this header for
     * @param  string       $authorization_value Auth string
     * @param  string       $date_time_string    Ymd\THis\Z encoded timestamp, getTimestamp()
     * @param  string       $service_target      Target service in the agcod string:
     *                                           Value like com.amazonaws.agcod.<sn>.<so>
     * @return array<mixed>                      Header data as array for curl request
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
     * The request string build with the actauly request data created by
     * getCanonicalRequest(). This string is used in the auth signature call
     *
     * @param  string $canonical_request_hash sha256 hash to build from
     * @return string                         String to send to buildAuthSignature()
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
     * Build the authentication signature used in the buildHeaders method
     *
     * @param  string $string_to_sign Data to sign, buildStringToSign()
     * @return string                 Authorized value as string
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
     * Build the derived key to build the final hmac signature string
     *
     * @param  bool   $rawOutput Set to true to create the hash based message
     *                           authenticator string as normal text string or
     *                           lowercase hexbits
     * @return string            Derived key (hmac type)
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
     * get the region based on endpoint
     * list as of 2021/10/20
     * WHERE            URL                                     REGION
     * North America    https://agcod-v2-gamma.amazon.com       us-east-1
     *                  https://agcod-v2.amazon.com
     * (US, CA, MX)
     * Europe and Asia  https://agcod-v2-eu-gamma.amazon.com    eu-west-1
     *                  https://agcod-v2-eu.amazon.com
     * (IT, ES, DE, FR, UK, TR, UAE, KSA, PL, NL, SE)
     * Far East         https://agcod-v2-fe-gamma.amazon.com    us-west-2
     *                  https://agcod-v2-fe.amazon.com
     * (JP, AU, SG)
     *
     * CURRENCY
     * USD for US
     * EUR for EU (IT, ES, DE, FR, PL, NL, SE)
     * JPY for JP
     * CAD for CA
     * AUD for AU
     * TRY for TR
     * AED for UAE
     * MXN for MX
     * GBP for UK
     *
     * @return string Region string depending on given endpoint url
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
                $region_name = 'eu-west-1';
                break;
            case 'agcod-v2-fe.amazon.com':
            case 'agcod-v2-fe-gamma.amazon.com':
                $region_name = 'us-west-2';
                break;
        }
        return $region_name;
    }


    /**
     * The actual data to send as json encoded string for creating a gift card.
     * The creation request id must be in the format:
     * <partner_id>_<unique id 13 characters>
     *
     * @param  float       $amount      Amount of currencty to create the gift card
     *                                  request for
     * @param  string|null $creation_id The creation id, if not set will be created here
     * @return string                   JSON encoded array to be used as payload
     *                                  in get gift card call
     */
    public function getGiftCardPayload(float $amount, ?string $creation_id = null): string
    {
        $payload = [
            'creationRequestId' => $creation_id ?: uniqid($this->config->getPartner() . '_'),
            'partnerId' => $this->config->getPartner(),
            'value' =>
                [
                    'currencyCode' => $this->config->getCurrency(),
                    'amount' => $amount
                ]
        ];
        return (json_encode($payload)) ?: '';
    }

    /**
     * The actual data to send as json encoded string to cancel a created gift card
     *
     * @param  string $creation_request_id Creation request id from previous get gift card
     * @param  string $gift_card_id        Gift card id from previous get gift card
     * @return string                      JSON encoded array to be used as payload
     *                                     in cancle gift card call
     */
    public function getCancelGiftCardPayload(string $creation_request_id, string $gift_card_id): string
    {
        $payload = [
            'creationRequestId' => $creation_request_id,
            'partnerId' => $this->config->getPartner(),
            'gcId' => $gift_card_id
        ];
        return (json_encode($payload)) ?: '';
    }

    /**
     * The actualy data to send as json encoded string for getting the current
     * account funds
     *
     * @return string JSON encoded array to be used as payload in funds call
     */
    public function getAvailableFundsPayload(): string
    {
        $payload = [
            'partnerId' => $this->config->getPartner(),
        ];
        return (json_encode($payload)) ?: '';
    }

    /**
     * Heeders used in the getCanonicalRequest()
     *
     * @param  string $service_operation Service operation code in the service string request
     *                                   Value is: com.amazonaws.agcod.AGCODService.<so>
     * @return string                    Header string to be used
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

    /**
     * Headers used in the get/cancel/funds requests
     *
     * @param  string $service_operation Service operation code to be used in header request
     *                                   and main request call
     * @param  string $payload           Payload from get/cancle Code or funds call
     * @return string                    Full POST service request code
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
     * Build sha256 hash from given data
     *
     * @param  string $data Data to be hashed with sha256
     * @return string       sha256 hash
     */
    public function buildHash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Create a sha256 based Hash-Based Message Authentication Code
     * with the given key and data
     *
     * @param  string $data Data to be hashed with key below
     * @param  string $key  Key to be used for creating the hash
     * @param  bool   $raw  Returning data as ascii string or hexibits
     * @return string       Hash-Based Message Authentication Code
     */
    public function hmac(string $data, string $key, bool $raw = true): string
    {
        return hash_hmac('sha256', $data, $key, $raw);
    }

    /**
     * Build timestamp in the format used by AWS services
     * eg 20211009\T102030\Z
     *
     * @return string date string based on current time. Ymd\THis\Z
     */
    public function getTimestamp()
    {
        return gmdate('Ymd\THis\Z');
    }

    /**
     * Get only the date string from the getTimestamp
     * eg 20211009
     *
     * @return string Date string YYYYmmdd extracted from getTimestamp()
     */
    public function getDateString()
    {
        return substr($this->getTimestamp(), 0, 8);
    }

    /**
     * Fixed content type for submission, is json
     *
     * @return string 'application/json' string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
}

// __END__
