<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/4/20
 * DESCRIPTION:
 * AmazonErrors Test
*/

declare(strict_types=1);

namespace test\phpUnit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use gullevek\AmazonIncentives;

#[CoversClass(\gullevek\AmazonIncentives\Exceptions\AmazonErrors::class)]
#[TestDox("\gullevek\AmazonIncentives\Exceptions\AmazonErrors")]
final class ExceptionsAmazonErrorsTest extends TestCase
{
	/**
	 * Just basic check for error message
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox("AmazonError getError() and decodeExceptionMessage()")]
	public function testAmazonErrorsDecodeExceptionMessage(): void
	{
		$error = AmazonIncentives\Exceptions\AmazonErrors::getError(
			error_status: 'ERROR',
			error_code: '999',
			error_type: 'TEST',
			message: 'Test error',
			_error_code: 1,
		);
		$error_decoded = AmazonIncentives\Exceptions\AmazonErrors::decodeExceptionMessage($error->getMessage());
		// ok, I know that in the log we will have log data, but for this test, reset them to null
		// they might be set from previous tests
		$error_decoded['log_id'] = null;
		$error_decoded['log'] = [];
		$this->assertArraysAreEqualIgnoringOrder(
			[
				'status' => 'ERROR',
				'code' => '999',
				'type' => 'TEST',
				'message' => 'Test error',
				'log_id' => null,
				'log' => []
			],
			$error_decoded
		);
	}

	#[Test]
	#[TestDox("AmazonError failed decodeExceptionMessage()")]
	public function testFailedAmazonErrorsDecodeExceptionMessag(): void
	{
		$error_decoded = AmazonIncentives\Exceptions\AmazonErrors::decodeExceptionMessage('some bad message');
		$this->assertArraysAreEqualIgnoringOrder(
			[
				'status' => '',
				'code' => '',
				'type' => '',
				'message' => 'some bad message',
				'log_id' => '',
				'log' => []
			],
			$error_decoded
		);
	}
}

// __END__
