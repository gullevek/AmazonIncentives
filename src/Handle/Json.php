<?php

/**
 * Handle json conversions
*/

declare(strict_types=1);

namespace gullevek\AmazonIncentives\Handle;

use gullevek\AmazonIncentives\Exceptions\AmazonErrors;

class Json
{
    /**
     * decode json string
     *
     * @param  string $json
     * @return array<mixed>
     * @throws AmazonErrors
     */
    public static function jsonDecode(string $json): array
    {
        $result = json_decode($json, true);
        if (
            ($json_last_error = json_last_error()) ||
            $result === null ||
            !is_array($result)
        ) {
            throw AmazonErrors::getError(
                'FAILURE',
                'J-' . $json_last_error,
                'jsonDecoreError',
                'Failed to decode json data',
                0
            );
        }
        return $result;
    }
}

// __END__
