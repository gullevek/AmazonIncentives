<?php

namespace gullevek\AmazonIncentives\Config;

interface ConfigInterface
{
	/**
	 * @return string
	 */
	public function getEndpoint(): string;

	/**
	 * @param string $endpoint
	 * @return ConfigInterface
	 */
	public function setEndpoint(string $endpoint): ConfigInterface;

	/**
	 * @return string
	 */
	public function getAccessKey(): string;

	/**
	 * @param string $key
	 * @return ConfigInterface
	 */
	public function setAccessKey(string $key): ConfigInterface;

	/**
	 * @return string
	 */
	public function getSecret(): string;

	/**
	 * @param string $secret
	 * @return ConfigInterface
	 */
	public function setSecret(string $secret): ConfigInterface;

	/**
	 * @return string
	 */
	public function getCurrency(): string;

	/**
	 * @param string $currency
	 * @return ConfigInterface
	 */
	public function setCurrency(string $currency): ConfigInterface;

	/**
	 * @return string
	 */
	public function getPartner(): string;

	/**
	 * @param string $partner
	 * @return ConfigInterface
	 */
	public function setPartner(string $partner): ConfigInterface;

	/**
	 * @return bool
	 */
	public function getDebug(): bool;

	/**
	 * @param bool $debug
	 * @return ConfigInterface
	 */
	public function setDebug(bool $debug): ConfigInterface;
}

// __END__
