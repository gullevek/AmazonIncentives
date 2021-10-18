<?php // phpcs:ignore PSR1.Files.SideEffects

declare(strict_types=1);

namespace Autoloader;

// shall implement an auto loader

if (class_exists('Autoload', false) === false) {
	// define the auto loader class
	class Autoload
	{
		// we do it simple here
		// passes on the class to load and we search here in namespace
		// to load that class
		public static function load($class)
		{
			// print "(1) Class: $class / DIR: " . __DIR__ . "<br>";
			// set directory seperator (we need to replace from namespace)
			$DS = DIRECTORY_SEPARATOR;
			// base lib
			$LIB = defined('LIB') ? LIB : '../src' . $DS;
			// if lib is in path at the end, do not add lib again
			// note that $LIB can have a directory seperator at the end
			// strip that out before we do a match
			$_LIB = rtrim($LIB, $DS);
			if (!preg_match("|$_LIB$|", __DIR__)) {
				$LIB .= $DS;
			} else {
				$LIB = '';
			}
			// default path is unset
			$path = false;
			// set path on full dir
			// if we have the namespace in the class, strip it out
			$len = 0;
			if (strpos($class, __NAMESPACE__) !== false) {
				$len = strlen(__NAMESPACE__);
			}
			// set default extension
			$extension = '.php';
			// set full include path
			$path = __DIR__ . $DS . $LIB . substr($class, $len);
			// replace namespace \ with dir sepeator
			$path = str_replace('\\', $DS, $path) . $extension;
			// print "(2) Class clean: $path<br>";
			// if path is set and a valid file
			if ($path !== false && is_file($path)) {
				// print "<b>(3)</b> Load Path: $path<br>";
				// we should sub that
				// self::loadFile($path);
				include $path;
				return true;
			}
			return false;
		}
		// end class define
	}

	spl_autoload_register('Autoloader\Autoload::load', true, true);
} // end check for already defined

// __END__
