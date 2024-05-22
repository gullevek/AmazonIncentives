<?php

declare(strict_types=1);

namespace test\phpUnit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use gullevek\AmazonIncentives;
use gullevek\dotEnv\DotEnv;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \gullevek\AmazonIncentives
 * @testdox \gullevek\AmazonIncentives full flow test
 */
final class AmazonIncentivesTest extends TestCase
{
    /** @var int wait tme in seconds between AWS side mock calls */
    private $mock_wait = 1;

    /**
     * Client curl exception testing
     *
     * @testdox AWS Incentives curl exception handling
     *
     * @return void
     */
    public function testAwsIncentivesCurlException(): void
    {
        // this is the exceptio we want
        $this->expectException(AmazonIncentives\Exceptions\AmazonErrors::class);
        // we don't need a class here, we just need client
        $client = new AmazonIncentives\Client\Client();
        // produce any error
        $client->request('invalid', [], '');
    }

    /**
     * curl/connection error checks
     *
     * @return array
     */
    public function amazonIncentivesProviderErrors(): array
    {
        // parameter data only for this
        // 0: url
        // 1: expected status
        // 2: expected code
        // 3: expected type
        return [
            // C001
            'C002 error' => [
                'url' => 'invalid',
                'expected_status' => 'FAILURE',
                'expected_error' => 'C002',
                'expected_type' => 'CurlError'
            ],
            // T001 timeout
            // 'T001 error' => [
            //     'url' => 'https://timeout.teq.jp',
            //     'expected_status' => 'RESEND',
            //     'expected_error' => 'T001',
            //     'expected_type' => 'RateExceeded'
            // ],
            // other error
            'E999 error' => [
                'url' => 'https://www.yahoo.co.jp',
                'expected_status' => 'FAILURE',
                'expected_error' => 'E999',
                'expected_type' => 'OtherUnknownError'
            ]
        ];
    }

    /**
     * Test errors thrown in Client class
     *
     * @dataProvider amazonIncentivesProviderErrors
     * @testdox AWS Incentives error handling [$_dataName]
     *
     * @param  string $url
     * @return void
     */
    public function testAwsIncentivesCurlErrors(
        string $url,
        string $expected_status,
        string $expected_error,
        string $expected_type
    ): void {
        // HANDLE:
        // * Init error
        //   - C001/Curl init error
        // * Client errors (C002)/false:
        //   - CURLE_COULDNT_CONNECT
        //   - CURLE_COULDNT_RESOLVE_HOST
        //   - CURLE_OPERATION_TIMEOUTED
        //   - CURLE_SSL_PEER_CERTIFICATE
        //   - 0/OTHER
        // * Client errors other
        //   - T001/Rate exceeded
        //   - E999/Other error

        // try/catch
        // -decodeExceptionMessage (static)

        // we don't need the full interface here, we just need client class
        $client = new AmazonIncentives\Client\Client();
        try {
            // set expected throw error
            $result = $client->request($url, [], '');
            $this->assertTrue(true, 'Successful client request');
        } catch (AmazonIncentives\Exceptions\AmazonErrors $e) {
            $curl_error = AmazonIncentives\Exceptions\AmazonErrors::decodeExceptionMessage($e->getMessage());
            // print "E-B: " . print_r($curl_error, true) . "\n";
            $this->assertEquals(
                $expected_status,
                $curl_error['status'],
                'Assert error status'
            );
            $this->assertEquals(
                $expected_error,
                $curl_error['code'],
                'Assert error code'
            );
            $this->assertEquals(
                $expected_type,
                $curl_error['type'],
                'Assert error type'
            );
        }
    }

    /**
     * init amazon incentive interface
     *
     * @param  array                             $connect
     * @param  bool                              $mock
     * @param  array|null                        $mock_response
     * @return AmazonIncentives\AmazonIncentives
     */
    private function awsIncentivesStartUp(
        array $connect,
        bool $mock,
        ?array $mock_response,
    ): AmazonIncentives\AmazonIncentives {
        $env_folder = $connect['env_folder'] ?? '';
        $env_file = $connect['env_file'] ?? '';
        $parameters = $connect['parameters'] ?? [];
        // reset _ENV always
        $_ENV = [];
        // env file read status
        $status = null;
        if (!empty($env_folder)) {
            if (!empty($env_file)) {
                $status = DotEnv::readEnvFile($env_folder, $env_file);
            } else {
                $status = DotEnv::readEnvFile($env_folder);
            }
        }

        // ENV must match _ENV vars if set
        if (!empty($env_folder) && $status != 0) {
            // abort with error
            $this->markTestSkipped(
                'Cannot read .env file needed for AWS tests: ' . $status
            );
        }

        // MOCK:
        // - for all buyGiftCard|cancelGiftCard|getAvailableFunds
        // WHAT:
        // \AWS->getCode|cancelCode|getBalance
        // -> \AWS->makeReqeust
        // -> NEW Client->request <= MOCK this
        // NOT MOCK:
        // any error calls in Client->request or exceptions

        if ($mock === true) {
            // create a new config with or without parameters
            $agcod_config = new AmazonIncentives\Config\Config(
                $parameters['key'] ?? null,
                $parameters['secret'] ?? null,
                $parameters['partner'] ?? null,
                $parameters['endpoint'] ?? null,
                $parameters['currency'] ?? null,
                $parameters['debug'] ?? null
            );

            // MOCK CLIENT
            // Master mock the Client class for request call
            // If we wan't to get errors thrown
            /** @var AmazonIncentives\Client\Client&MockObject */
            $client_mock = $this->createPartialMock(AmazonIncentives\Client\Client::class, ['request']);
            // set the needed return here
            $client_mock->method('request')->willReturn(json_encode($mock_response));

            // MOCK AWS and attache above class in client return
            /** @var AmazonIncentives\AWS\AWS&MockObject */
            $aws_mock = $this->getMockBuilder(AmazonIncentives\AWS\AWS::class)
                ->setConstructorArgs([$agcod_config])
                ->onlyMethods(['newClient'])
                ->getMock();
            // attach mocked client
            $aws_mock->method('newClient')->willReturn($client_mock);

            // MOCK AMAZONINCENTIVES
            /** @var AmazonIncentives\AmazonIncentives&MockObject */
            $agcod = $this->getMockBuilder(AmazonIncentives\AmazonIncentives::class)
                ->setConstructorArgs([
                    $parameters['key'] ?? null,
                    $parameters['secret'] ?? null,
                    $parameters['partner'] ?? null,
                    $parameters['endpoint'] ?? null,
                    $parameters['currency'] ?? null,
                    $parameters['debug'] ?? null
                ])
                ->onlyMethods(['newAWS'])
                ->getMock();
            // attach mocked AWS class
            $agcod->method('newAWS')->willReturn($aws_mock);
        } else {
            // if we mock, we mock the Client->request
            $agcod = new AmazonIncentives\AmazonIncentives(
                $parameters['key'] ?? null,
                $parameters['secret'] ?? null,
                $parameters['partner'] ?? null,
                $parameters['endpoint'] ?? null,
                $parameters['currency'] ?? null,
                $parameters['debug'] ?? null
            );
        }

        return $agcod;
    }

    /**
     * Holds the configs for loading data from .env for parameter
     *
     * @return array
     */
    public function awsIncentivesProvider(): array
    {
        // 0: .env file folder
        // 1: .env file name (if not set use .env)
        // 2: parameters that override _ENV variables
        return [
            // this is with real test account data
            'env_test' => [
                'env_folder' => __DIR__ . DIRECTORY_SEPARATOR . '..',
                'env_file' => null,
                'parameters' => null
            ],
            // this is for mocking only
            'parameter_dummy' => [
                'env_folder' => null,
                'env_file' => null,
                'parameters' => [
                    null,
                    null,
                    null,
                    'http://i.dont.exist.at.all',
                    'JPY'
                ]
            ],
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function amazonIncentivesProviderGetFunds(): array
    {
        // remove final keyword
        // BypassFinals::enable();
        // get connectors
        $connectors = $this->awsIncentivesProvider();
        // 0: connect array (env file, env folder, parameters array)
        // 1: mock or normal call
        // 2: if mock connect response must be defined here
        // 3: exepcted response array
        return [
            'non mock test data' => [
                'connect' => $connectors['env_test'],
                'mock' => false,
                'mock_response' => null,
                'expected' => [
                    //
                ]
            ],
            'mock data test' => [
                'connect' => $connectors['parameter_dummy'],
                'mock' => true,
                'mock_response' => [
                    'availableFunds' => [
                        'amount' => 0.0,
                        'currencyCode' => 'JPY',
                    ],
                    'status' => 'SUCCESS',
                    'timestamp' => '20220610T085450Z',
                ],
            ],
        ];
    }

    /**
     * Undocumented function
     *
     * @dataProvider amazonIncentivesProviderGetFunds
     * @testdox AWS Incentives get available funds [$_dataName]
     *
     * @param  array      $connect
     * @param  bool       $mock
     * @param  array|null $mock_response
     * @return void
     */
    public function testAwsIncentivesGetAvailableFunds(
        array $connect,
        bool $mock,
        ?array $mock_response
    ): void {
        // load class
        $agcod = $this->awsIncentivesStartUp(
            $connect,
            $mock,
            $mock_response,
        );

        // - getAvailableFunds: get available fund
        //   - getStatus
        //   - getAmount
        //   - getCurrency
        //   - getTimestamp
        $funds = $agcod->getAvailableFunds();
        // if not mock do type check
        // if mock do matching check from mcok
        if ($mock === false) {
            $this->assertEquals(
                'SUCCESS',
                $funds->getStatus(),
                'Assert status is success'
            );
            // numeric number
            $this->assertIsNumeric(
                $funds->getAmount(),
                'Assert amoount is numerc'
            );
            // USD, JPY, etc
            $this->assertIsString(
                $funds->getCurrency(),
                'Assert currency is string'
            );
            // 20220610T085450Z
            $this->assertMatchesRegularExpression(
                "/^\d{8}T\d{6}Z$/",
                $funds->getTimestamp(),
                'Assert timestamp matches regex'
            );
        } else {
            $this->assertEquals(
                $mock_response['status'],
                $funds->getStatus(),
                'Assert mock status'
            );
            $this->assertEquals(
                $mock_response['availableFunds']['amount'],
                $funds->getAmount(),
                'Assert mock amount'
            );
            $this->assertEquals(
                $mock_response['availableFunds']['currencyCode'],
                $funds->getCurrency(),
                'Assert mock currency code'
            );
            $this->assertEquals(
                $mock_response['timestamp'],
                $funds->getTimestamp(),
                'Assert mock timestamp'
            );
        }
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function amazonIncentivesProviderBuy(): array
    {
        // get connectors
        $connectors = $this->awsIncentivesProvider();
        // 0: connect array (env file, env folder, parameters array)
        // 1: mock or normal call
        // 2: if mock connect response must be defined here
        // 3: exepcted response array
        // 4: value in float
        return [
            'non mock test data' => [
                'connect' => $connectors['env_test'],
                'mock' => false,
                'mock_response' => null,
                'amount' => 500.0,
            ],
            'mock data test' => [
                'connect' => $connectors['parameter_dummy'],
                'mock' => true,
                'mock_response' => [
                    'cardInfo' => [
                        'cardNumber' => null,
                        'cardStatus' => 'Fulfilled',
                        'expirationDate' => null,
                        'value' => [
                            'amount' => 1000.0,
                            'currencyCode' => 'JPY',
                        ],
                    ],
                    'creationRequestId' => 'PartnerId_62a309167e7a4',
                    'gcClaimCode' => 'LJ49-AKDUV6-UYCP',
                    'gcExpirationDate' => 'Thu Jun 10 14:59:59 UTC 2032',
                    'gcId' => '5535125272070255',
                    'status' => 'SUCCESS',
                ],
                'amount' => 1000.0,
            ],
        ];
    }

    /**
     * Undocumented function
     *
     * @dataProvider amazonIncentivesProviderBuy
     * @testdox AWS Incentives buy gift card [$_dataName]
     *
     * @param  array      $connect
     * @param  bool       $mock
     * @param  array|null $mock_response
     * @param  float      $amount
     * @return void
     */
    public function testAwsIncentivesBuyGiftCard(
        array $connect,
        bool $mock,
        ?array $mock_response,
        float $amount
    ): void {
        // - init plain
        // * via ::make()

        // - buyGiftCard: buy gift card
        //   - getCreationRequestId
        //   - getId
        //   - getClaimCode
        //   - getExpirationDate
        //   - getStatus

        // load class
        $agcod = $this->awsIncentivesStartUp(
            $connect,
            $mock,
            $mock_response,
        );

        $response = $agcod->buyGiftCard($amount);

        if ($mock === false) {
            // type check
            $this->assertEquals(
                'SUCCESS',
                $response->getStatus(),
                'Assert status'
            );
            // creation request id must start with partner id
            $this->assertStringStartsWith(
                $agcod->checkMe()['CONFIG']->getPartner(),
                $response->getCreationRequestId(),
                'Assert creation request id starts with partner id'
            );
            // gift card id is number
            $this->assertIsString(
                $response->getId(),
                'Assert gift card id is numeric'
            );
            // claim code is 4-6-4 alphanumeric
            $this->assertIsString(
                $response->getClaimCode(),
                'Assert claim code is string'
            );
            // card status
            $this->assertEquals(
                'Fulfilled',
                $response->getCardStatus(),
                'Assert card status'
            );
            // value/amount of gitft
            $this->assertEquals(
                $amount,
                $response->getValue(),
                'Assert card amount value'
            );
            // check currency
            $this->assertEquals(
                $agcod->checkMe()['CONFIG']->getCurrency(),
                $response->getCurrency(),
                'Assert card amount currency'
            );
            // only for requests outside US/Australia cards
            // expiration date: Thu Jun 10 14:59:59 UTC 2032
            $this->assertMatchesRegularExpression(
                "/^[A-Z]{1}[a-z]{2} [A-Z]{1}[a-z]{2} \d{1,2} \d{1,2}:\d{1,2}:\d{1,2} [A-Z]{3} \d{4}$/",
                $response->getExpirationDate(),
                'Assert expiration date regex'
            );
        } else {
            // value match to mock response
            $this->assertEquals(
                $mock_response['status'],
                $response->getStatus(),
                'Assert mock status'
            );
            $this->assertEquals(
                $mock_response['cardInfo']['cardStatus'],
                $response->getCardStatus(),
                'Assert mock card status'
            );
            $this->assertEquals(
                $mock_response['cardInfo']['value']['amount'],
                $response->getValue(),
                'Assert mock card amount value'
            );
            $this->assertEquals(
                $mock_response['cardInfo']['value']['currencyCode'],
                $response->getCurrency(),
                'Assert mock card amount currency'
            );
            $this->assertEquals(
                $mock_response['creationRequestId'],
                $response->getCreationRequestId(),
                'Assert mock creation request id'
            );
            $this->assertEquals(
                $mock_response['gcId'],
                $response->getId(),
                'Assert mock gift card id'
            );
            $this->assertEquals(
                $mock_response['gcClaimCode'],
                $response->getClaimCode(),
                'Assert mock claim code'
            );
            $this->assertEquals(
                $mock_response['gcExpirationDate'],
                $response->getExpirationDate(),
                'Assert mock expiration date'
            );
        }
    }

    /**
     * Buy a gift card and use same creation request id to get another gift card
     * has to return same data ggain
     *
     * @dataProvider amazonIncentivesProviderBuy
     * @testdox AWS Incentives buy gift card and again with same creation request id [$_dataName]
     *
     * @param  array      $connect
     * @param  bool       $mock
     * @param  array|null $mock_response
     * @param  float      $amount
     * @return void
     */
    public function testAwsIncentivesSameBuyGiftCard(
        array $connect,
        bool $mock,
        ?array $mock_response,
        float $amount
    ): void {
        // load class
        $agcod = $this->awsIncentivesStartUp(
            $connect,
            $mock,
            $mock_response,
        );
        // get one
        $response_a = $agcod->buyGiftCard($amount);
        // get one again with same code
        $response_b = $agcod->buyGiftCard($amount, $response_a->getCreationRequestId());

        // a and b must be equalt
        $this->assertEquals(
            $response_a->getStatus(),
            $response_b->getStatus(),
            'Assert status'
        );
        $this->assertEquals(
            $response_a->getCardStatus(),
            $response_b->getCardStatus(),
            'Assert card status'
        );
        $this->assertEquals(
            $response_a->getValue(),
            $response_b->getValue(),
            'Assert card amount value'
        );
        $this->assertEquals(
            $response_a->getCurrency(),
            $response_b->getCurrency(),
            'Assert card amount currency'
        );
        $this->assertEquals(
            $response_a->getCreationRequestId(),
            $response_b->getCreationRequestId(),
            'Assert creation request id'
        );
        $this->assertEquals(
            $response_a->getId(),
            $response_b->getId(),
            'Assert gift card id'
        );
        $this->assertEquals(
            $response_a->getClaimCode(),
            $response_b->getClaimCode(),
            'Assert claim code'
        );
        $this->assertEquals(
            $response_a->getExpirationDate(),
            $response_b->getExpirationDate(),
            'Assert expiration date'
        );
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function amazonIncentivesProviderCancel(): array
    {
        // get connectors
        $connectors = $this->awsIncentivesProvider();
        // 0: connect array (env file, env folder, parameters array)
        // 1: mock or normal call
        // 2: if mock connect response must be defined here
        // 3: exepcted response array
        return [
            'non mock test data' => [
                'connect' => $connectors['env_test'],
                'mock' => false,
                'mock_response' => null,
            ],
            'mock data test' => [
                'connect' => $connectors['parameter_dummy'],
                'mock' => true,
                'mock_response' => [
                    'creationRequestId' => 'PartnerId_62a309167e7a4',
                    'gcId' => '5535125272070255',
                    'status' => 'SUCCESS',
                ],
            ],
        ];
    }

    /**
     * Cancel a bought gift card
     *
     * @dataProvider amazonIncentivesProviderCancel
     * @testdox AWS Incentives cancel gift card [$_dataName]
     *
     * @param  array      $connect
     * @param  bool       $mock
     * @param  array|null $mock_response
     * @return void
     */
    public function testAwsIncentivesCancelGiftCard(
        array $connect,
        bool $mock,
        ?array $mock_response
    ): void {
        // - cancelGiftCard: cancel gift card
        // load class
        $agcod = $this->awsIncentivesStartUp(
            $connect,
            $mock,
            $mock_response,
        );

        if ($mock === false) {
            // get a gift card, then cancel it
            $purchase = $agcod->buyGiftCard(500.0);
            $response = $agcod->cancelGiftCard(
                $purchase->getCreationRequestId(),
                $purchase->getId()
            );
            $this->assertEquals(
                'SUCCESS',
                $response->getStatus(),
                'Assert mock status'
            );
            // creation request id must start with partner id
            $this->assertStringStartsWith(
                $agcod->checkMe()['CONFIG']->getPartner(),
                $response->getCreationRequestId(),
                'Assert creation request id starts with partner id'
            );
            // gift card id is number
            $this->assertIsString(
                $response->getId(),
                'Assert gift card id is numeric'
            );
        } else {
            $response = $agcod->cancelGiftCard(
                $mock_response['creationRequestId'],
                $mock_response['gcId']
            );
            $this->assertEquals(
                $mock_response['status'],
                $response->getStatus(),
                'Assert mock status'
            );
            $this->assertEquals(
                $mock_response['creationRequestId'],
                $response->getCreationRequestId(),
                'Assert mock creation request id'
            );
            $this->assertEquals(
                $mock_response['gcId'],
                $response->getId(),
                'Assert mock gift card id'
            );
        }
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function amazonIncentivesProviderRefunded(): array
    {
        // get connectors
        $connectors = $this->awsIncentivesProvider();
        // 0: connect array (env file, env folder, parameters array)
        // 1: mock or normal call
        // 2: if mock connect response must be defined here
        // 3: exepcted response array
        return [
            'non mock test data' => [
                'connect' => $connectors['env_test'],
                'mock' => false,
                'mock_response' => null,
            ],
            'mock data test' => [
                'connect' => $connectors['parameter_dummy'],
                'mock' => true,
                'mock_response' => [
                    'cardInfo' => [
                        'cardNumber' => null,
                        'cardStatus' => 'RefundedToPurchaser',
                        'expirationDate' => null,
                        'value' => [
                            'amount' => 1000.0,
                            'currencyCode' => 'JPY',
                        ],
                    ],
                    'gcClaimCode' => 'LJ49-AKDUV6-UYCP',
                    'creationRequestId' => 'PartnerId_62a309167e7a4',
                    'gcId' => '5535125272070255',
                    'status' => 'SUCCESS',
                ],
            ],
        ];
    }

    /**
     * Undocumented function
     *
     * @dataProvider amazonIncentivesProviderRefunded
     * @testdox AWS Incentives request cancled gift card [$_dataName]
     *
     * @param  array      $connect
     * @param  bool       $mock
     * @param  array|null $mock_response
     * @return void
     */
    public function testAwsIncentivesRequestRefundedGiftCard(
        array $connect,
        bool $mock,
        ?array $mock_response
    ): void {
        // load class
        $agcod = $this->awsIncentivesStartUp(
            $connect,
            $mock,
            $mock_response,
        );

        if ($mock === false) {
            // get a gift card
            $purchase = $agcod->buyGiftCard(500.0);
            // then cancel it
            $agcod->cancelGiftCard(
                $purchase->getCreationRequestId(),
                $purchase->getId()
            );
            // buy again with same getCreationRequestId id will now have
            $purchase_again = $agcod->buyGiftCard(500.0, $purchase->getCreationRequestId());
            // should return like purchase put with RefundedToPurchaser
            $this->assertEquals(
                'RefundedToPurchaser',
                $purchase_again->getCardStatus(),
                'Assert gift card purchased again after cancel with same code'
            );
        } else {
            $response = $agcod->buyGiftCard(500.0);
            $this->assertEquals(
                $mock_response['cardInfo']['cardStatus'],
                $response->getCardStatus(),
                'Assert mock card status'
            );
        }
    }

    /**
     * list of AWS mock codes for AWS side mock testing
     *
     * @return array
     */
    public function awsIncentivesMockProvider(): array
    {
        return [
            'successMock' => [
                'creation_request_id' => 'F0000',
                'return_code' => '',
                'status' => 'SUCCESS'
            ],
            'SimpleAmountIsNull' => [
                'creation_request_id' => 'F1000',
                'return_code' => 'F100',
                'status' => 'FAILURE'
            ],
            'InvalidAmountInput' => [
                'creation_request_id' => 'F2003',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'InvalidAmountValue' => [
                'creation_request_id' => 'F2004',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'InvalidCurrencyCodeInput' => [
                'creation_request_id' => 'F2005',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'CardActivatedWithDifferentRequestId' => [
                'creation_request_id' => 'F2010',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'MaxAmountExceeded' => [
                'creation_request_id' => 'F2015',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'CurrencyCodeMismatch' => [
                'creation_request_id' => 'F2016',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'FractionalAmountNotAllowed' => [
                'creation_request_id' => 'F2017',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'CancelRequestArrivedAfterTimeLimit' => [
                'creation_request_id' => 'F2047',
                'return_code' => 'F200',
                'status' => 'FAILURE'
            ],
            'InsufficientFunds' => [
                'creation_request_id' => 'F3003',
                'return_code' => 'F300',
                'status' => 'FAILURE'
            ],
            'AccountHasProblems' => [
                'creation_request_id' => 'F3005',
                'return_code' => 'F300',
                'status' => 'FAILURE'
            ],
            'CustomerSurpassedDailyVelocityLimit' => [
                'creation_request_id' => 'F3010',
                'return_code' => 'F300',
                'status' => 'FAILURE'
            ],
            'SystemTemporarilyUnavailable' => [
                'creation_request_id' => 'F4000',
                'return_code' => 'F400',
                'status' => 'RESEND'
            ],
            'UnknownError' => [
                'creation_request_id' => 'F5000',
                'return_code' => 'F500',
                'status' => 'FAILURE'
            ],
        ];
    }

    /**
     * NOTE: Must have a valid test user connection setup
     * This only works with a valid server connection.
     * Runs through AWS Incentives mock values and checks the return code and status
     *
     * @dataProvider awsIncentivesMockProvider
     * @testdox AWS Incentives Mock $creation_request_id will be $expected_status with $expected_code [$_dataName]
     *
     * @return void
     */
    public function testAwsIncentivesWithMocks(
        string $creation_request_id,
        string $expected_code,
        string $expected_status,
    ): void {
        // reset _ENV for reading
        $_ENV = [];
        // read the .env file
        $status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '..');
        // if loading failed, abort
        if ($status != 0) {
            // abort with error
            $this->markTestSkipped(
                'Cannot read .env file needed for AWS mock tests: ' . $status
            );
        }
        // if no value set, set to 500
        $value = $_ENV['AWS_MOCK_VALUE'] ?? 500;
        // run tests
        try {
            $aws_gcod = AmazonIncentives\AmazonIncentives::make()->buyGiftCard(
                (float)$value,
                $creation_request_id
            );
            $this->assertEquals(
                $expected_status,
                $aws_gcod->getStatus(),
                'Assert status ok in AWS GCOD mocks'
            );
        } catch (\Exception $e) {
            $error = AmazonIncentives\Exceptions\AmazonErrors::decodeExceptionMessage($e->getMessage());
            $this->assertEquals(
                [
                    'code' => $expected_code,
                    'status' => $expected_status,
                ],
                [
                    'code' => $error['code'],
                    'status' => $error['status'],
                ],
                'Assert status failed in AWS GCOD mocks'
            );
        }
        // wait a moment between tests
        sleep($this->mock_wait);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function checkMeProvider(): array
    {
        // 0: .env file folder
        // 1: .env file name (if not set use .env)
        // 2: parameters that override _ENV variables
        return [
            'default all empty' => [
                'use_env' => null,
                'env_file' => null,
                'parameters' => null,
            ],
            'set parameters' => [
                'env_folder' => null,
                'env_file' => null,
                'parameters' => [
                    'key' => 'key',
                    'secret' => 'secret',
                    'partner' => 'partner id',
                    'endpoint' => 'https://endpoint.test.com',
                    'currency' => 'currency',
                    'debug' => true,
                ],
                'expected' => [],
            ],
            'load from env' => [
                'env_folder' => __DIR__ . DIRECTORY_SEPARATOR . '..',
                'env_file' => null,
                'parameters' => null,
            ],
            'load from env, but override parameter' => [
                'env_folder' => __DIR__ . DIRECTORY_SEPARATOR . '..',
                'env_file' => null,
                'parameters' => [
                    'key' => 'key',
                    'secret' => 'secret',
                    'partner' => 'partner id',
                    'endpoint' => 'https://endpoint.test.com',
                    'currency' => 'currency',
                ]
            ]
            // test missing parameter, set vie _ENV
        ];
    }

    /**
     * Check the checkMe function that will work with or without any settings
     * passed on.
     * This also tests basic loading
     * - parseing for endoint as url
     * - override check for _ENV vs parameter
     *
     * @cover ::checkMe
     * @dataProvider checkMeProvider
     * @testdox AmazonIncentives tests [$_dataName]
     *
     * @param  string|null $env_folder
     * @param  string|null $env_file
     * @param  array|null  $parameters
     * @return void
     */
    public function testCheckMe(?string $env_folder, ?string $env_file, ?array $parameters): void
    {
        // reset _ENV before each run to avoid nothing to load errors
        $_ENV = [];
        // env load status
        $status = null;
        if (!empty($env_folder)) {
            if (!empty($env_file)) {
                $status = DotEnv::readEnvFile($env_folder, $env_file);
            } else {
                $status = DotEnv::readEnvFile($env_folder);
            }
        }
        if (!empty($parameters)) {
            $aws = new AmazonIncentives\AmazonIncentives(
                $parameters['key'],
                $parameters['secret'],
                $parameters['partner'],
                $parameters['endpoint'],
                $parameters['currency'],
                $parameters['debug'] ?? null,
            );
        } else {
            $aws = new AmazonIncentives\AmazonIncentives();
        }
        $aws_check_me = $aws->checkMe();
        // ENV must match _ENV vars if set
        if (!empty($env_folder) && $status != 0) {
            // abort with error
            $this->markTestSkipped(
                'Cannot read .env file needed: ' . $status
            );
        } elseif (!empty($env_folder)) {
            $this->assertEquals(
                $_ENV,
                $aws_check_me['ENV'],
                'Assert _ENV set equal'
            );
        }
        // compare that data matches
        // print "CM: " . print_r($aws_check_me, true) . "\n";
        // CONFIG must match to parameters or ENV, parsed host name check
        $this->assertEquals(
            // parameter > _ENV -> empty
            !empty($parameters['partner']) ?
                $parameters['partner'] :
                $_ENV['AWS_GIFT_CARD_PARTNER_ID'] ?? '',
            $aws_check_me['CONFIG']->getPartner(),
            'Assert config matching input'
        );
        // KEY must match access_key/AWS_GIFT_CARD_KEY
        $this->assertEquals(
            $aws_check_me['CONFIG']->getAccessKey(),
            $aws_check_me['KEY'],
            'Assert access key m'
        );
    }
}

// __END__
