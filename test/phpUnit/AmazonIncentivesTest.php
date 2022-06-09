<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use gullevek\AmazonIncentives\AmazonIncentives;
use gullevek\dotEnv\DotEnv;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \gullevek\AmazonIncentives
 * @testdox \gullevek\AmazonIncentives full flow test
 */
final class AmazonIncentivesTest extends TestCase
{
	public function amazonIncentivesProvider(): array
	{
		return [
			'empty' => [
				'env_folder' => null,
				'env_file' => null,
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider amazonIncentivesProvider
	 * @testdox AmazonIncentives tests [$_dataName]
	 *
	 * @return void
	 */
	public function testAmazonIncentives(?string $env_folder, ?string $env_file): void
	{
		// - init plain
		// * via ::make()
		// - buyGiftCard: buy gift card
		//   - getCreationRequestId
		//   - getId
		//   - getClaimCode
		//   - getExpirationDate
		//   - getStatus
		// - cancelGiftCard: cancel gift card
		// - getAvailableFunds: get available fund
		//   - getAmount
		//   - getCurrency
		//   - getTimestamp

		// try/catch
		// -decodeExceptionMessage (static)
		$this->markTestSkipped('Not yet implemented: AmazonIncentives');
	}

	public function checkMeProvider(): array
	{
		return [
			'default' => [
				'env_folder' => null,
				'env_file' => null,
				'expected' => [],
			]
		];
	}

	/**
	 * Undocumented function
	 *
	 * @cover ::checkMe
	 * @dataProvider checkMeProvider
	 * @testdox AmazonIncentives tests [$_dataName]
	 *
	 * @return void
	 */
	public function testCheckMe(?string $env_folder, ?string $env_file, array $expected): void
	{
		$aws = new AmazonIncentives();
		$aws_check_me = $aws->checkMe();
		// compare that data matches
		print "CM: " . print_r($aws_check_me, true) . "\n";
	}
}

// __END__
