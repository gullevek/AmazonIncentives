<?php

namespace gullevek\AmazonIncentives\Client;

interface ClientInterface
{
	/**
	 * @param string       $url     The URL being requested, including domain and protocol
	 * @param array        $headers Headers to be used in the request
	 * @param array|string $params  Can be nested for arrays and hashes
	 *
	 * @return String
	 */

	public function request(string $url, array $headers, $params): string;
}

// __END__
