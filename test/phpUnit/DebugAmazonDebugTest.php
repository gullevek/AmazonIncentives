<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/4/20
 * DESCRIPTION:
 * AmazonDebug Test
*/

declare(strict_types=1);

namespace test\phpUnit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use gullevek\AmazonIncentives;

#[CoversClass(\gullevek\AmazonIncentives\Debug\AmazonDebug::class)]
#[TestDox("\gullevek\AmazonIncentives\Debug\AmazonDebug")]
#[BackupStaticProperties(true)]
final class DebugAmazonDebugTest extends TestCase
{
	/**
	 * setup for class test
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$items = new \ReflectionProperty(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'log');
		$items->setValue(null, []);
		$items = new \ReflectionProperty(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'debug');
		$items->setValue(null, false);
		$items = new \ReflectionProperty(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'id');
		$items->setValue(null, null);
	}

	#[Test]
	#[TestDox('AmazonDebug setDebug()')]
	public function testDebugFlag(): void
	{
		AmazonIncentives\Debug\AmazonDebug::setDebug(false);
		$this->assertEquals(
			false,
			AmazonIncentives\Debug\AmazonDebug::getDebug()
		);
		$this->assertEquals(
			null,
			AmazonIncentives\Debug\AmazonDebug::getId()
		);
		AmazonIncentives\Debug\AmazonDebug::setDebug(true);
		$this->assertEquals(
			true,
			AmazonIncentives\Debug\AmazonDebug::getDebug()
		);
		$this->assertNotEquals(
			null,
			AmazonIncentives\Debug\AmazonDebug::getId()
		);
	}

	#[Test]
	#[TestDox('AmazonDebug getId()')]
	public function testGetId(): void
	{
		AmazonIncentives\Debug\AmazonDebug::setDebug(true, 'DebugId');
		$this->assertEquals(
			'DebugId',
			AmazonIncentives\Debug\AmazonDebug::getId()
		);
	}

	#[Test]
	#[TestDox('AmazonDebug writeLog(')]
	public function testWriteLog(): void
	{
		AmazonIncentives\Debug\AmazonDebug::setDebug(false);
		AmazonIncentives\Debug\AmazonDebug::writeLog(['test' => 'foo']);
		$this->assertEquals(
			[],
			AmazonIncentives\Debug\AmazonDebug::getLog()
		);
		AmazonIncentives\Debug\AmazonDebug::setDebug(true, 'DebugId');
		AmazonIncentives\Debug\AmazonDebug::writeLog(['test' => 'foo']);
		$this->assertEquals(
			['DebugId' => [['test' => 'foo']]],
			AmazonIncentives\Debug\AmazonDebug::getLog()
		);
		$this->assertEquals(
			[['test' => 'foo']],
			AmazonIncentives\Debug\AmazonDebug::getLog('DebugId')
		);
	}

	#[Test]
	#[TestDox('AmazonDebug cover setId abort with no debug')]
	public function testSetIdReturnsEarlyWhenDebugIsFalse(): void
	{
		// Ensure debug is false
		$debugProp = new \ReflectionProperty(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'debug');
		$debugProp->setValue(null, false);

		// Capture the current $id value before calling
		$idPropBefore = new \ReflectionProperty(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'id');
		$idBefore = $idPropBefore->getValue(null);

		// Call the private static method via Reflection
		$method = new \ReflectionMethod(\gullevek\AmazonIncentives\Debug\AmazonDebug::class, 'setId');
		$method->invoke(null, 'test-id'); // null = static method

		// $id should NOT have changed because we returned early
		$idAfter = $idPropBefore->getValue(null);

		$this->assertSame($idBefore, $idAfter);
	}
}

// __END__
