<?php

namespace Amazon\Config;

class Config implements ConfigInterface
{
	/**
	 * @var string
	 */
	private $endpoint;
	/**
	 * @var string
	 */
	private $access_key;
	/**
	 * @var string
	 */
	private $secret_key;
	/**
	 * @var string
	 */
	private $partner_id;
	/**
	 * @var string
	 */
	private $currency;
	/**
	 * @var bool
	 */
	private $debug;

	/**
	 * @param string|null $key
	 * @param string|null $secret
	 * @param string|null $partner
	 * @param string|null $endpoint
	 * @param string|null $currency
	 */
	public function __construct(
		?string $key,
		?string $secret,
		?string $partner,
		?string $endpoint,
		?string $currency,
		?bool $debug,
	) {
		$this->setAccessKey($key ?: $_ENV['AWS_GIFT_CARD_KEY'] ?? '');
		$this->setSecret($secret ?: $_ENV['AWS_GIFT_CARD_SECRET'] ?? '');
		$this->setPartner($partner ?: $_ENV['AWS_GIFT_CARD_PARTNER_ID'] ?? '');
		$this->setEndpoint($endpoint ?: $_ENV['AWS_GIFT_CARD_ENDPOINT'] ?? '');
		$this->setCurrency($currency ?: $_ENV['AWS_GIFT_CARD_CURRENCY'] ?? '');
		$this->setDebug($debug ?: (!empty($_ENV['AWS_DEBUG']) ? true : false));
	}

	/**
	 * @return string
	 */
	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	/**
	 * @param string $endpoint
	 * @return ConfigInterface
	 */
	public function setEndpoint(string $endpoint): ConfigInterface
	{
		// TODO: check valid endpoint + set region
		$this->endpoint = parse_url($endpoint, PHP_URL_HOST);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessKey(): string
	{
		return $this->access_key;
	}

	/**
	 * @param string $key
	 * @return ConfigInterface
	 */
	public function setAccessKey(string $key): ConfigInterface
	{
		$this->access_key = $key;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSecret(): string
	{
		return $this->secret_key;
	}

	/**
	 * @param string $secret
	 * @return ConfigInterface
	 */
	public function setSecret(string $secret): ConfigInterface
	{
		$this->secret_key = $secret;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 * @return ConfigInterface
	 */
	public function setCurrency(string $currency): ConfigInterface
	{
		// TODO: check currency valid + currenc valid for region
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPartner(): string
	{
		return $this->partner_id;
	}

	/**
	 * @param string $partner
	 * @return ConfigInterface
	 */
	public function setPartner(string $partner): ConfigInterface
	{
		$this->partner_id = $partner;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getDebug(): bool
	{
		return $this->debug;
	}

	/**
	 * @param bool $debug
	 * @return ConfigInterface
	 */
	public function setDebug(bool $debug): ConfigInterface
	{
		$this->debug = $debug;

		return $this;
	}
}

// __END__
