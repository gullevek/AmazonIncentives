<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for ACL\Login
 * @coversDefaultClass \gullevek\DotEnv
 * @testdox \gullevek\DotEnv method tests
 */
final class DotEnvTest extends TestCase
{
	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function envFileProvider(): array
	{
		$dot_env_content = [
			'SOMETHING' => 'A',
			'OTHER' => 'B IS B',
			'Complex' => 'A B \"D is F',
			'HAS_SPACE' => 'ABC',
			'FAILURE' => 'ABC',
			'SIMPLEBOX' => 'A B  C',
			'TITLE' => '1',
			'FOO' => '1.2',
			'SOME.TEST' => 'Test Var',
			'SOME.LIVE' => 'Live Var',
			'Test' => 'A',
			'TEST' => 'B',
			'LINE' => "ABC\nDEF",
			'OTHERLINE' => "ABC\nAF\"ASFASDF\nMORESHIT",
			'SUPERLINE' => '',
			'__FOO_BAR_1' => 'b',
			'__FOOFOO' => 'f     ',
			123123 => 'number',
			'EMPTY' => '',
		];
		// 0: folder relative to test folder, if unset __DIR__
		// 1: file, if unset .env
		// 2: status to be returned
		// 3: _ENV file content to be set
		// 4: override chmod as octect in string
		return [
			'default' => [
				'folder' => null,
				'file' => null,
				'status' => 3,
				'content' => [],
				'chmod' => null,
			],
			'cannot open file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'cannot_read.env',
				'status' => 2,
				'content' => [],
				'chmod' => '000',
			],
			'empty file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'empty.env',
				'status' => 1,
				'content' => [],
				'chmod' => null,
			],
			'override all' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'test.env',
				'status' => 0,
				'content' => $dot_env_content,
				'chmod' => null,
			],
			'override directory' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => null,
				'status' => 0,
				'content' => $dot_env_content,
				'chmod' => null,
			],
		];
	}

	/**
	 * test read .env file
	 *
	 * @covers ::readEnvFile
	 * @dataProvider envFileProvider
	 * @testdox Read _ENV file from $folder / $file with expected status: $expected_status [$_dataName]
	 *
	 * @param  string|null $folder
	 * @param  string|null $file
	 * @param  int         $expected_status
	 * @param  array       $expected_env
	 * @param  string|null $chmod
	 * @return void
	 */
	public function testReadEnvFile(
		?string $folder,
		?string $file,
		int $expected_status,
		array $expected_env,
		?string $chmod
	): void {
		// if we have file + chmod set
		$old_chmod = null;
		if (
			is_file($folder . DIRECTORY_SEPARATOR . $file) &&
			!empty($chmod)
		) {
			// get the old permissions
			$old_chmod = fileperms($folder . DIRECTORY_SEPARATOR . $file);
			chmod($folder . DIRECTORY_SEPARATOR . $file, octdec($chmod));
		}
		if ($folder !== null && $file !== null) {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile($folder, $file);
		} elseif ($folder !== null) {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile($folder);
		} else {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile();
		}
		$this->assertEquals(
			$status,
			$expected_status,
			'Assert returned status equal'
		);
		// now assert read data
		$this->assertEquals(
			$_ENV,
			$expected_env,
			'Assert _ENV correct'
		);
		// if we have file and chmod unset
		if ($old_chmod !== null) {
			chmod($folder . DIRECTORY_SEPARATOR . $file, $old_chmod);
		}
	}
}

// __END__
