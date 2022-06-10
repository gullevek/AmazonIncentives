<?php

namespace gullevek\AmazonIncentives\Exceptions;

use RuntimeException;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

final class AmazonErrors extends RuntimeException
{
	/**
	 * Returns an Runtime exception including a json encoded string with all
	 * parameters including last log id and log
	 *
	 * @param  string       $error_status agcodResponse->status from Amazon
	 * @param  string       $error_code   errorCode from Amazon
	 * @param  string       $error_type   errorType from Amazon
	 * @param  string       $message      Message string to ad
	 * @param  int          $_error_code  Error code to set
	 * @return AmazonErrors               Exception Class
	 */
	public static function getError(
		string $error_status,
		string $error_code,
		string $error_type,
		string $message,
		int $_error_code
	): self {
		// NOTE: if xdebug.show_exception_trace is set to 1 this will print ERRORS
		return new static(
			(json_encode([
				'status' => $error_status,
				'code' => $error_code,
				'type' => $error_type,
				'message' => $message,
				// atach log data if exists
				'log_id' => AmazonDebug::getId(),
				'log' => AmazonDebug::getLog(),
			])) ?: 'AmazonErrors: json encode problem: ' . $message,
			$_error_code
		);
	}
}

// __END__
