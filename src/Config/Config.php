<?php

namespace gullevek\AmazonIncentives\Config;

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
		$this->setAccessKey(($key) ?: $this->parseEnv('AWS_GIFT_CARD_KEY')); /** @phpstan-ignore-line */
		$this->setSecret(($secret) ?: $this->parseEnv('AWS_GIFT_CARD_SECRET')); /** @phpstan-ignore-line */
		$this->setPartner(($partner) ?: $this->parseEnv('AWS_GIFT_CARD_PARTNER_ID')); /** @phpstan-ignore-line */
		$this->setEndpoint(($endpoint) ?: $this->parseEnv('AWS_GIFT_CARD_ENDPOINT')); /** @phpstan-ignore-line */
		$this->setCurrency(($currency) ?: $this->parseEnv('AWS_GIFT_CARD_CURRENCY')); /** @phpstan-ignore-line */
		$this->setDebug(($debug) ?: $this->parseEnv('AWS_DEBUG')); /** @phpstan-ignore-line */
	}

	/**
	 * string key to search, returns entry from _ENV
	 * if not matchin key, returns empty
	 *
	 * @param string       $key To search in _ENV array
	 * @return string|bool      Returns either string or true/false (DEBUG flag)
	 */
	private function parseEnv(string $key)
	{
		$return = '';
		switch ($key) {
			case 'AWS_DEBUG':
				$return = !empty($_ENV['AWS_DEBUG']) ? true : false;
				break;
			case 'AWS_GIFT_CARD_KEY':
			case 'AWS_GIFT_CARD_SECRET':
			case 'AWS_GIFT_CARD_PARTNER_ID':
			case 'AWS_GIFT_CARD_ENDPOINT':
			case 'AWS_GIFT_CARD_CURRENCY':
				$return = $_ENV[$key] ?? '';
				break;
			default:
				break;
		}
		return $return;
	}

	/**
	 * @return string|null
	 */
	public function getEndpoint(): ?string
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
		$this->endpoint = (parse_url($endpoint, PHP_URL_HOST)) ?: '';

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAccessKey(): ?string
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
	 * @return string|null
	 */
	public function getSecret(): ?string
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
	 * @return string|null
	 */
	public function getCurrency(): ?string
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
	 * @return string|null
	 */
	public function getPartner(): ?string
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
