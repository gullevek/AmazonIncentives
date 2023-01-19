<?php

/*
 * Amazon Incentive Code
 * Amazon Gift Code on Demand
 */

namespace gullevek\AmazonIncentives;

use gullevek\AmazonIncentives\AWS\AWS;
use gullevek\AmazonIncentives\Config\Config;
use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

final class AmazonIncentives
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AmazonGiftCode constructor.
     *
     * @param string|null $key      Account key
     * @param string|null $secret   Secret key
     * @param string|null $partner  Partner ID
     * @param string|null $endpoint Endpoint URL including https://
     * @param string|null $currency Currency type. Eg USD, JPY, etc
     * @param bool|null $debug      Debug flag
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
        // init debug
        AmazonDebug::setDebug($this->config->getDebug());
    }

    // *********************************************************************
    // PRIVATE HELPER METHODS
    // *********************************************************************

    // *********************************************************************
    // PUBLIC METHODS
    // *********************************************************************

    /**
     * Buy a gift card
     *
     * @param  float       $value               Amount to purchase a gift card
     *                                          in currency value
     * @param  string|null $creation_request_id Override automatically created request id
     *                                          If not set will create a new one, or
     *                                          return data for created one
     * @return Response\CreateResponse Returns new created response object or
     *                                 previous created if creation_request_id was used
     *
     * @throws AmazonErrors
     */
    public function buyGiftCard(float $value, string $creation_request_id = null): Response\CreateResponse
    {
        return ($this->newAWS())->getCode($value, $creation_request_id);
    }


    /**
     * Cancel a previous created gift card, if within the time frame
     *
     * @param string $creation_request_id Previous created request id from buyGiftCard
     * @param string $gift_card_id        Previous gift card id from buyGiftCard (gcId)
     * @return Response\CancelResponse Returns the cancled request object
     *
     * @throws AmazonErrors
     */
    public function cancelGiftCard(string $creation_request_id, string $gift_card_id): Response\CancelResponse
    {
        return ($this->newAWS())->cancelCode($creation_request_id, $gift_card_id);
    }

    /**
     * Gets the current funds in this account
     *
     * @return Response\CreateBalanceResponse Returns the account funds object
     *
     * @throws AmazonErrors
     */
    public function getAvailableFunds(): Response\CreateBalanceResponse
    {
        return ($this->newAWS())->getBalance();
    }

    /**
     * AmazonIncentives creates own client and returns it as static object
     *
     * @param string|null $key      Account key
     * @param string|null $secret   Secret key
     * @param string|null $partner  Partner ID
     * @param string|null $endpoint Endpoint URL including https://
     * @param string|null $currency Currency type. Eg USD, JPY, etc
     * @param bool|null $debug      Debug flag
     * @return AmazonIncentives     self class
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
     * wrapper to create new AWS class.
     * used in all buy/cancel/get calss
     *
     * @return AWS Main AWS worker class
     */
    public function newAWS(): AWS
    {
        return new AWS($this->config);
    }

    /**
     * Decodes the Exception message body
     * Returns an array with code (Amazon error codes), type (Amazon error info)
     * message (Amazon returned error message string)
     *
     * @param  string $message Exception message json string
     * @return array<mixed>    Decoded with code, type, message fields
     *
     * @deprecated use \gullevek\AmazonIncentives\Exceptions\AmazonErrors::decodeExceptionMessage()
     */
    public static function decodeExceptionMessage(string $message): array
    {
        return AmazonErrors::decodeExceptionMessage($message);
    }

    // *********************************************************************
    // PUBLIC TEST METHODS
    // *********************************************************************

    /**
     * Prints out ENV, CONFIG and KEY data
     * This is for debug only, this will print out secrets.
     * Use with care
     *
     * @return array<mixed>
     */
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
