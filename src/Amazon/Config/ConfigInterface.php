<?php

namespace Amazon\Config;

interface ConfigInterface
{
	/**
	 * @return String
	 */
	public function getEndpoint(): string;

	/**
	 * @param string $endpoint
	 * @return $this
	 */
	public function setEndpoint(string $endpoint): ConfigInterface;

	/**
	 * @return String
	 */
	public function getAccessKey(): string;

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setAccessKey(string $key): ConfigInterface;

	/**
	 * @return String
	 */
	public function getSecret(): string;

	/**
	 * @param string $secret
	 * @return $this
	 */
	public function setSecret(string $secret): ConfigInterface;

	/**
	 * @return String
	 */
	public function getCurrency(): string;

	/**
	 * @param string $currency
	 * @return $this
	 */
	public function setCurrency(string $currency): ConfigInterface;

	/**
	 * @return String
	 */
	public function getPartner(): string;

	/**
	 * @param string $partner
	 * @return $this
	 */
	public function setPartner(string $partner): ConfigInterface;
}

// __END__
