<?php

declare(strict_types=1);

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
                // attach log data if exists
                'log_id' => AmazonDebug::getId(),
                'log' => AmazonDebug::getLog(),
            ])) ?: 'AmazonErrors: json encode problem: ' . $message,
            $_error_code
        );
    }

    /**
     * Decodes the Exception message body
     * Returns an array with code (Amazon error codes), type (Amazon error info)
     * message (Amazon returned error message string)
     *
     * @param  string $message Exception message json string
     * @return array<mixed>    Decoded with code, type, message fields
     */
    public static function decodeExceptionMessage(string $message): array
    {
        $message_ar = json_decode($message, true);
        // if we have an error, build empty block and only fill message
        if (json_last_error() || $message_ar === null || !is_array($message_ar)) {
            $message_ar = [
                'status' => '',
                'code' => '',
                'type' => '',
                'message' => $message,
                'log_id' => '',
                'log' => []
            ];
        }
        return $message_ar;
    }
}

// __END__
