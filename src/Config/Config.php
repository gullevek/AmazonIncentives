<?php

namespace gullevek\AmazonIncentives\Config;

class Config implements ConfigInterface
{
	/** @var string Endpoint URL without https:// */
	private $endpoint = '';
	/** @var string Access Key */
	private $access_key = '';
	/** @var string Secret Key */
	private $secret_key = '';
	/** @var string Partner ID */
	private $partner_id = '';
	/** @var string Currency type as USD, JPY, etc */
	private $currency = '';
	/** @var bool Debug flag on or off */
	private $debug = false;

	/**
	 * @param string|null $key      Access key
	 * @param string|null $secret   Secret Key
	 * @param string|null $partner  Partner ID
	 * @param string|null $endpoint Endpoing URL including https://
	 * @param string|null $currency Currency to use, see valid list on AWS documentation.
	 *                              valid names are like USD, JPY, etc
	 * @param bool|null   $debug    Debug flag
	 */
	public function __construct(
		?string $key,
		?string $secret,
		?string $partner,
		?string $endpoint,
		?string $currency,
		?bool $debug
	) {
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setAccessKey(($key) ?: $this->parseEnv('AWS_GIFT_CARD_KEY'));
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setSecret(($secret) ?: $this->parseEnv('AWS_GIFT_CARD_SECRET'));
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setPartner(($partner) ?: $this->parseEnv('AWS_GIFT_CARD_PARTNER_ID'));
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setEndpoint(($endpoint) ?: $this->parseEnv('AWS_GIFT_CARD_ENDPOINT'));
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setCurrency(($currency) ?: $this->parseEnv('AWS_GIFT_CARD_CURRENCY'));
		/**
		 * @psalm-suppress InvalidScalarArgument
		 * @phpstan-ignore-next-line
		 */
		$this->setDebug(($debug) ?: $this->parseEnv('AWS_DEBUG'));
	}

	/**
	 * string key to search, returns entry from _ENV
	 * if not matchin key, returns empty
	 *
	 * @param  string       $key To search in _ENV array
	 * @return string|bool       Returns either string or true/false (DEBUG flag)
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
				$return = (string)($_ENV[$key] ?? '');
				break;
			default:
				break;
		}
		return $return;
	}

	/**
	 * @return string Returns current set endpoint, without https://
	 */
	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	/**
	 * @param  string          $endpoint Full endpoint url with https://
	 * @return ConfigInterface           Class interface (self)
	 */
	public function setEndpoint(string $endpoint): ConfigInterface
	{
		// TODO: check valid endpoint + set region
		$this->endpoint = (parse_url($endpoint, PHP_URL_HOST)) ?: '';

		return $this;
	}

	/**
	 * @return string Current access key
	 */
	public function getAccessKey(): string
	{
		return $this->access_key;
	}

	/**
	 * @param  string          $key Access Key to set
	 * @return ConfigInterface      Class interface (self)
	 */
	public function setAccessKey(string $key): ConfigInterface
	{
		$this->access_key = $key;

		return $this;
	}

	/**
	 * @return string Current secret key
	 */
	public function getSecret(): string
	{
		return $this->secret_key;
	}

	/**
	 * @param  string          $secret Secret key to set
	 * @return ConfigInterface         Class interface (self)
	 */
	public function setSecret(string $secret): ConfigInterface
	{
		$this->secret_key = $secret;

		return $this;
	}

	/**
	 * @return string Current set currency
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @param  string          $currency Currency to set (eg USD, JPY, etc)
	 * @return ConfigInterface           Class interface (self)
	 */
	public function setCurrency(string $currency): ConfigInterface
	{
		// TODO: check currency valid + currenc valid for region
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @return string Current set partner id
	 */
	public function getPartner(): string
	{
		return $this->partner_id;
	}

	/**
	 * @param  string          $partner Partner id to set
	 * @return ConfigInterface          Class interface (self)
	 */
	public function setPartner(string $partner): ConfigInterface
	{
		$this->partner_id = $partner;

		return $this;
	}

	/**
	 * @return bool Current set debug flag as bool
	 */
	public function getDebug(): bool
	{
		return $this->debug;
	}

	/**
	 * @param  bool            $debug Set debug flag as bool
	 * @return ConfigInterface        Class interface (self)
	 */
	public function setDebug(bool $debug): ConfigInterface
	{
		$this->debug = $debug;

		return $this;
	}
}

// __END__
