<?php

declare(strict_types=1);

namespace gullevek\AmazonIncentives\Config;

interface ConfigInterface
{
    /**
     * @return string Returns current set endpoint, without https://
     */
    public function getEndpoint(): string;

    /**
     * @param  string          $endpoint Full endpoint url with https://
     * @return ConfigInterface           Class interface (self)
     */
    public function setEndpoint(string $endpoint): ConfigInterface;

    /**
     * @return string Current access key
     */
    public function getAccessKey(): string;

    /**
     * @param  string          $key Access Key to set
     * @return ConfigInterface      Class interface (self)
     */
    public function setAccessKey(string $key): ConfigInterface;

    /**
     * @return string Current secret key
     */
    public function getSecret(): string;

    /**
     * @param  string          $secret Secret key to set
     * @return ConfigInterface         Class interface (self)
     */
    public function setSecret(string $secret): ConfigInterface;

    /**
     * @return string Current set currency
     */
    public function getCurrency(): string;

    /**
     * @param  string          $currency Currency to set (eg USD, JPY, etc)
     * @return ConfigInterface           Class interface (self)
     */
    public function setCurrency(string $currency): ConfigInterface;

    /**
     * @return string Current set partner id
     */
    public function getPartner(): string;

    /**
     * @param  string          $partner Partner id to set
     * @return ConfigInterface          Class interface (self)
     */
    public function setPartner(string $partner): ConfigInterface;

    /**
     * @return bool Current set debug flag as bool
     */
    public function getDebug(): bool;

    /**
     * @param  bool            $debug Set debug flag as bool
     * @return ConfigInterface        Class interface (self)
     */
    public function setDebug(bool $debug): ConfigInterface;
}

// __END__
