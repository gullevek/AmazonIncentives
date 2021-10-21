<?php

// simple write all into an array that we can poll in the return group
// to activate AmazonDebug::setDebug(true) must be called once

namespace gullevek\AmazonIncentives\Debug;

class AmazonDebug
{
	/**
	 * @var array<mixed>
	 */
	private static $log = [];
	/**
	 * @var bool
	 */
	private static $debug = false;
	/**
	 * @var string|null
	 */
	private static $id = null;

	/**
	 * set the ID for current run
	 * if debug is off, nothing will be set and id is null
	 * This is run on setFlag, if debug is true
	 *
	 * @param  string|null $id If not set, will default to uniqid() call
	 * @return void
	 */
	private static function setId(?string $id = null): void
	{
		if (self::$debug === false) {
			return;
		}
		if ($id === null) {
			$id = uniqid();
		}
		self::$id = $id;
	}

	/**
	 * set the debug flag.
	 * This is automatically run in gullevek\AmazonIncentives\AmazonIncentives::__construct
	 * No need to run manuall
	 *
	 * @param  boolean     $debug Can only be True or False
	 * @param  string|null $id    If not set, will default to uniqid() call
	 * @return void
	 */
	public static function setDebug(bool $debug, ?string $id = null): void
	{
		self::$debug = $debug;
		if (self::$debug === false) {
			return;
		}
		self::setId($id);
	}

	/**
	 * returns current debug flag status
	 *
	 * @return boolean True if debug is on, False if debug is off
	 */
	public static function getDebug(): bool
	{
		return self::$debug;
	}

	/**
	 * get the current set ID, can return null if debug is off
	 *
	 * @return string|null Current set ID for this log run
	 */
	public static function getId(): ?string
	{
		return self::$id;
	}

	/**
	 * write a log entry
	 * Data is as array key -> value
	 * Will be pushed as new array entry int log
	 * Main key is the set Id for this run
	 *
	 * @param  array<mixed> $data Any array data to store in the log
	 * @return void
	 */
	public static function writeLog(array $data): void
	{
		if (self::$debug === false) {
			return;
		}
		self::$log[self::getId() ?? ''][] = $data;
	}

	/**
	 * get all logs written since first class run
	 * or get all log entries for given ID
	 *
	 * @param  string|null $id If set returns only this id logs
	 *                         or empty array if not found
	 * @return array<mixed>    Always array, empty if not data or not found
	 */
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
