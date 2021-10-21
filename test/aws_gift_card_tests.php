<?php // phpcs:ignore PSR1.Files.SideEffects

// Tests for Amazon Gift Card Incentives

/**
 * write log as string from array data
 * includes timestamp
 *
 * @param  array<mixed> $data Debug log array data to add to the json string
 * @return string
 */
function writeLog(array $data): string
{
	return json_encode([
		'date' => date('Y-m-d H:i:s'),
		'log' => $data
	]) . "\n";
}

/**
 * translate the UTC amazon date string to Y-m-d H:i:s standard
 *
 * @param  string $date A UTC string date from Amazon
 * @return string
 */
function dateTr(string $date): string
{
	return date('Y-m-d H:i:s', (strtotime($date)) ?: null);
}

/**
 * print exception string
 *
 * @param  string       $call_request Call request, eg buyGiftCard
 * @param  integer      $error_code   $e Exception error code
 * @param  array<mixed> $error        Array from the Exception message json string
 * @param  boolean      $debug_print  If we should show the debug log
 * @return void
 */
function printException(
	string $call_request,
	int $error_code,
	array $error,
	bool $debug_print
): void {
	print "AWS: " . $call_request . ": " . $error['status']
		. " [" . $error_code . "]: "
		. $error['code'] . " | " . $error['type']
		. " | " . $error['message'];
	if ($debug_print === true) {
		print "<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
	}
}

// composer auto loader
$loader = require '../vendor/autoload.php';
// need to add this or it will not load here
$loader->addPsr4('gullevek\\', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
// print "LOADER: <pre>" . print_r($loader, true) . "</pre>";
// env file loader (simple)
require 'read_env_file.php';

use gullevek\AmazonIncentives\AmazonIncentives;

// load env data with dotenv
readEnvFile(__DIR__);

print "<h1>Amazon Gift Card Incentives</h1><br>";

// must have set
// endpoint/region: AWS_GIFT_CARD_ENDPOINT
// aws key: AWS_GIFT_CARD_KEY
// aws secret: AWS_GIFT_CARD_SECRET
// partner id: AWS_GIFT_CARD_PARTNER_ID
// currency: AWS_ICENTIVE_CURRENCY
// optional
// debug: AWS_DEBUG (if not set: off)

// as in .env
// AWS_GIFT_CARD_ENDPOINT.TEST
// AWS_GIFT_CARD_ENDPOINT.LIVE

define('LOCATION', 'test');
foreach (
	[
		'AWS_GIFT_CARD_KEY', 'AWS_GIFT_CARD_SECRET', 'AWS_GIFT_CARD_PARTNER_ID',
		'AWS_GIFT_CARD_ENDPOINT', 'AWS_GIFT_CARD_CURRENCY', 'AWS_DEBUG'
	] as $key
) {
	//
	$_ENV[$key] = $_ENV[$key . '.' . strtoupper((LOCATION))] ?? $_ENV[$key] ?? '';
}

// open debug file output
$fp = fopen('log/debug.' . date('YmdHis') . '.log', 'w');
if (!is_resource($fp)) {
	die("Cannot open log debug file");
}

// run info test (prints ENV vars)
$run_info_test = false;
// run test to get funds info
$run_fund_test = true;
// run the normal get/cancel gift card tests
$run_gift_tests = true;
// run mock error check tests
$run_mocks = false;

// should we print debug info
$debug_print = false;
// how long to wait between each call
$debug_wait = 2;
// if set to true will print all the debug logs too
$mock_debug = false;
// wait in seconds between mock tests
$mock_wait = 2;

if ($run_info_test === true) {
	$aws = new AmazonIncentives();
	print "checkMe: <pre>" . print_r($aws->checkMe(), true) . "</pre>";
	fwrite($fp, writeLog($aws->checkMe()));
	print "<hr>";
}

// check balance
if ($run_fund_test === true) {
	try {
		$aws_test = AmazonIncentives::make()->getAvailableFunds();
		print "AWS: getAvailableFunds: " .  $aws_test->getStatus() . ": "
			. "Amount: " . $aws_test->getAmount() . ", "
			. "Currency: " . $aws_test->getCurrency() . ", "
			. "Timestamp: " . $aws_test->getTimestamp();
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (Exception $e) {
		$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
		printException('getAvailableFunds', $e->getCode(), $error, $debug_print);
		fwrite($fp, writeLog($error));
	};
	print "<br>";
	sleep($debug_wait);
	// print "LOG: <pre>" . print_r($aws_test->getLog(), true) . "</pre><br>";
	print "<hr>";
}

if ($run_gift_tests === true) {
	// create card
	$value = 1000;
	$creation_request_id = '';
	$gift_card_id = '';
	try {
		// we must be sure we pass FLOAT there
		$aws_test = AmazonIncentives::make()->buyGiftCard((float)$value);
		$creation_request_id = $aws_test->getCreationRequestId();
		$gift_card_id = $aws_test->getId();
		$claim_code = $aws_test->getClaimCode();
		$expiration_date = $aws_test->getExpirationDate();
		$request_status = $aws_test->getStatus();
		print "AWS: buyGiftCard: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "EXPIRE DATE: <b>" . dateTr($expiration_date) . "</b>, "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
		printException('buyGiftCard', $e->getCode(), $error, $debug_print);
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);
	try {
		// cancel above created card card
		$aws_test = AmazonIncentives::make()->cancelGiftCard($creation_request_id, $gift_card_id);
		$request_status = $aws_test->getStatus();
		print "AWS: cancelGiftCard: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id;
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: cancelGiftCard: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'];
		if ($debug_print === true) {
			print "<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);

	// set same request ID twice to get same response test
	try {
		$aws_test = AmazonIncentives::make()->buyGiftCard((float)$value);
		$creation_request_id = $aws_test->getCreationRequestId();
		$gift_card_id = $aws_test->getId();
		$claim_code = $aws_test->getClaimCode();
		$expiration_date = $aws_test->getExpirationDate();
		$request_status = $aws_test->getStatus();
		print "AWS: buyGiftCard: CODE A: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "EXPIRE DATE: <b>" . dateTr($expiration_date) . "</b>, "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
		printException('cancelGiftCard', $e->getCode(), $error, $debug_print);
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);
	try {
		$aws_test = AmazonIncentives::make()->buyGiftCard((float)$value, $creation_request_id);
		$request_status = $aws_test->getStatus();
		// same?
		$claim_code = $aws_test->getClaimCode();
		$expiration_date = $aws_test->getExpirationDate();
		print "AWS: buyGiftCard: SAME CODE A AGAIN: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "EXPIRE DATE: <b>" . dateTr($expiration_date) . "</b>, "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
		printException('buyGiftCard', $e->getCode(), $error, $debug_print);
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	print "<hr>";
	sleep($debug_wait);
}

// MOCK TEST
if ($run_mocks === true) {
	$mock_ok = '<span style="color:green;">MOCK OK</span>';
	$mock_failure = '<span style="color:red;">MOCK FAILURE</span>';
	$mock_value = 500;
	$mock = [];

	$mock['F0000'] = [ 'ret' => '', 'st' => 'SUCCESS']; // success mock
	$mock['F1000'] = [ 'ret' => 'F100', 'st' => 'FAILURE']; // SimpleAmountIsNull, etc
	$mock['F2003'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // InvalidAmountInput
	$mock['F2004'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // InvalidAmountValue
	$mock['F2005'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // InvalidCurrencyCodeInput
	$mock['F2010'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // CardActivatedWithDifferentRequestId
	$mock['F2015'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // MaxAmountExceeded
	$mock['F2016'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // CurrencyCodeMismatch
	$mock['F2017'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // FractionalAmountNotAllowed
	$mock['F2047'] = [ 'ret' => 'F200', 'st' => 'FAILURE']; // CancelRequestArrivedAfterTimeLimit
	$mock['F3003'] = [ 'ret' => 'F300', 'st' => 'FAILURE']; // InsufficientFunds
	$mock['F3005'] = [ 'ret' => 'F300', 'st' => 'FAILURE']; // AccountHasProblems
	$mock['F3010'] = [ 'ret' => 'F300', 'st' => 'FAILURE']; // CustomerSurpassedDailyVelocityLimit
	$mock['F4000'] = [ 'ret' => 'F400', 'st' => 'RESEND']; // SystemTemporarilyUnavailable
	$mock['F5000'] = [ 'ret' => 'F500', 'st' => 'FAILURE']; // UnknownError

	foreach ($mock as $creation_id => $mock_return) {
		print "<b>TS: " . microtime() . "</b>: ";
		try {
			$aws_test = AmazonIncentives::make()->buyGiftCard((float)$mock_value, $creation_id);
			$creation_request_id = $aws_test->getCreationRequestId();
			$gift_card_id = $aws_test->getId();
			$claim_code = $aws_test->getClaimCode();
			$request_status = $aws_test->getStatus();
			print "AWS: MOCK: " . $creation_id . ": buyGiftCard: <b>" . $request_status . "</b>: "
				. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
				. "CLAIM CODE: <b>" . $claim_code . "</b>: ";
			if ($mock_return['st'] == $request_status) {
				print $mock_ok;
			} else {
				print $mock_failure;
			}
			if ($mock_debug === true) {
				print "<pre>" . print_r($aws_test, true) . "</pre>";
			}
			fwrite($fp, writeLog((array)$aws_test));
		} catch (Exception $e) {
			$error = AmazonIncentives::decodeExceptionMessage($e->getMessage());
			print "AWS: MOCK: " . $creation_id . ": buyGiftCard: " . $error['status']
				. " [" . $e->getCode() . "]: "
				. $error['code'] . " | " . $error['type']
				. " | " . $error['message'] . ": ";
			if (
				$mock_return['ret'] == $error['code'] &&
				$mock_return['st'] == $error['status']
			) {
				print $mock_ok;
			} else {
				print $mock_failure;
			}
			if ($mock_debug === true) {
				print "<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
			}
			fwrite($fp, writeLog($error));
		}
		print "<br>";
		// Waiting a moment, so we don't flood
		sleep($mock_wait);
	}
	print "<hr>";
}

fclose($fp);

// __END__
