<?php

namespace gullevek\AmazonIncentives\Client;

use gullevek\AmazonIncentives\Exceptions\AmazonErrors;
use gullevek\AmazonIncentives\Debug\AmazonDebug;

class Client implements ClientInterface
{
    /** @var int instead of JsonResponse::HTTP_OK */
    private const HTTP_OK = 200;

    /**
     * Makes an request to the target url via curl
     * Returns result as string (json)
     *
     * @param  string              $url     The URL being requested,
     *                                      including domain and protocol
     * @param  array<mixed>        $headers Headers to be used in the request
     * @param  array<mixed>|string $params  Can be nested for arrays and hashes
     * @return string                       Result as json string
     */
    public function request(string $url, array $headers, $params): string
    {
        $handle = curl_init($url);
        if ($handle === false) {
            // throw Error here with all codes
            throw AmazonErrors::getError(
                'FAILURE',
                'C001',
                'CurlInitError',
                'Failed to init curl with url: ' . $url,
                0
            );
        }
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($handle, CURLOPT_FAILONERROR, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($handle);

        if ($result === false) {
            $err = curl_errno($handle);
            $message = curl_error($handle);
            $this->handleCurlError($url, $err, $message);
        }

        if (curl_getinfo($handle, CURLINFO_HTTP_CODE) !== self::HTTP_OK) {
            $err = curl_errno($handle);
            AmazonDebug::writeLog(['CURL_REQUEST_RESULT' => $result]);
            // extract all the error codes from Amazon
            $result_ar = json_decode((string)$result, true);
            // if message is 'Rate exceeded', set different error
            if (($result_ar['message'] ?? '') == 'Rate exceeded') {
                $error_status = 'RESEND';
                $error_code = 'T001';
                $error_type = 'RateExceeded';
                $message = $result_ar['message'] ?? 'Rate exceeded';
            } else {
                // for all other error messages
                $error_status = $result_ar['agcodResponse']['status'] ?? 'FAILURE';
                $error_code = $result_ar['errorCode'] ?? 'E999';
                $error_type = $result_ar['errorType'] ?? 'OtherUnknownError';
                $message = $result_ar['message'] ?? 'Unknown error occured';
            }
            // throw Error here with all codes
            throw AmazonErrors::getError(
                $error_status,
                $error_code,
                $error_type,
                $message,
                $err
            );
        }
        return (string)$result;
    }

    /**
     * handles any CURL errors and throws an error with the correct
     * error message
     *
     * @param  string $url     The url that was originaly used
     * @param  int    $errno   Error number from curl handler
     * @param  string $message The error message string from curl
     * @return void
     */
    private function handleCurlError(string $url, int $errno, string $message): void
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $message = 'Could not connect to AWS (' . $url . '). Please check your '
                    . 'internet connection and try again. [' . $message . ']';
                break;
            case CURLE_SSL_PEER_CERTIFICATE:
                $message = 'Could not verify AWS SSL certificate. Please make sure '
                    . 'that your network is not intercepting certificates. '
                    . '(Try going to ' . $url . 'in your browser.) '
                    . '[' . $message . ']';
                break;
            case 0:
            default:
                $message = 'Unexpected error communicating with AWS: ' . $message;
        }

        // throw an error like in the normal reqeust, but set to CURL error
        throw AmazonErrors::getError(
            'FAILURE',
            'C002',
            'CurlError',
            $message,
            $errno
        );
    }
}

// __END__
