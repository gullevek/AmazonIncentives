<?php

// simple write all into an array that we can poll in the return group

namespace Amazon\Debug;

class AmazonDebug
{
	private static $log = [];
	private static $debug = false;
	private static $id = null;

	public function __construct()
	{
	}

	public static function setId(?string $id = null): void
	{
		if (self::$debug === false) {
			return;
		}
		if ($id === null) {
			$id = uniqid();
		}
		self::$id = $id;
	}

	public static function getId(): string
	{
		return self::$id;
	}

	public static function setFlag(bool $debug): void
	{
		self::$debug = $debug;
	}

	public static function writeLog(array $data): void
	{
		if (self::$debug === false) {
			return;
		}
		self::$log[self::$id][] = $data;
	}

	public static function getLog(?string $id = null): array
	{
		if ($id === null) {
			return self::$log;
		} else {
			return self::$log[$id] ?? [];
		}
	}
}

// __END__
