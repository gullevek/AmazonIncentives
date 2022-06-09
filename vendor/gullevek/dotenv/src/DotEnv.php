<?php

declare(strict_types=1);

namespace gullevek\dotEnv;

class DotEnv
{
	/**
	 * parses .env file
	 *
	 * Rules for .env file
	 * variable is any alphanumeric string followed by = on the same line
	 * content starts with the first non space part
	 * strings can be contained in "
	 * strings MUST be contained in " if they are multiline
	 * if string starts with " it will match until another " is found
	 * anything AFTER " is ignored
	 * if there are two variables with the same name only the first is used
	 * variables are case sensitive
	 *
	 * @param  string $path     Folder to file, default is __DIR__
	 * @param  string $env_file What file to load, default is .env
	 * @return int              -1 other error
	 *                          0 for success full load
	 *                          1 for file loadable, but no data inside
	 *                          2 for file not readable or open failed
	 *                          3 for file not found
	 */
	public static function readEnvFile(
		string $path = __DIR__,
		string $env_file = '.env'
	): int {
		// default -1;
		$status = -1;
		$env_file_target = $path . DIRECTORY_SEPARATOR . $env_file;
		// this is not a file -> abort
		if (!is_file($env_file_target)) {
			$status = 3;
			return $status;
		}
		// cannot open file -> abort
		if (!is_readable($env_file_target)) {
			$status = 2;
			return $status;
		}
		// open file
		if (($fp = fopen($env_file_target, 'r')) === false) {
			$status = 2;
			return $status;
		}
		// set to readable but not yet any data loaded
		$status = 1;
		$block = false;
		$var = '';
		while ($line = fgets($fp)) {
			// main match for variable = value part
			if (preg_match("/^\s*([\w_.]+)\s*=\s*((\"?).*)/", $line, $matches)) {
				$var = $matches[1];
				$value = $matches[2];
				$quotes = $matches[3];
				// write only if env is not set yet, and write only the first time
				if (empty($_ENV[$var])) {
					if (!empty($quotes)) {
						// match greedy for first to last so we move any " if there are
						if (preg_match('/^"(.*[^\\\])"/U', $value, $matches)) {
							$value = $matches[1];
						} else {
							// this is a multi line
							$block = true;
							// first " in string remove
							// add removed new line back because this is a multi line
							$value = ltrim($value, '"') . PHP_EOL;
						}
					}
					// if block is set, we strip line of slashes
					$_ENV[$var] = $block === true ? stripslashes($value) : $value;
					// set successful load
					$status = 0;
				}
			} elseif ($block === true) {
				// read line until there is a unescaped "
				// this also strips everything after the last "
				if (preg_match("/(.*[^\\\])\"/", $line, $matches)) {
					$block = false;
					// strip ending " and EVERYTHING that follows after that
					$line = $matches[1];
				}
				// strip line of slashes
				$_ENV[$var] .= stripslashes($line);
			}
		}
		fclose($fp);
		return $status;
	}
}


// __END__
