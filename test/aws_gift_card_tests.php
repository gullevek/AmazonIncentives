<?php // phpcs:ignore PSR1.Files.SideEffects

// Tests for Amazon Gift Card Incentives

/**
 * write log as string from array data
 * includes timestamp
 *
 * @param array $data
 * @return string
 */
function writeLog(array $data): string
{
	return json_encode([
		'date' => date('Y-m-d H:i:s'),
		'log' => $data
	]) . "\n";
}

// general auto loader
require 'autoloader.php';
// env file loader
require 'read_env_file.php';

// load env data with dotenv
readEnvFile(__DIR__);

print "<h1>Amazon Gift Card Incentives</h1><br>";

// must have set
// endpoint/region: AWS_GIFT_CARD_ENDPOINT
// aws key: AWS_GIFT_CARD_KEY
// aws secret: AWS_GIFT_CARD_SECRET
// partner id: AWS_GIFT_CARD_PARTNER_ID
// optional
// currency: AWS_ICENTIVE_CURRENCY

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

/*
	ENDPOINTS:

- remove '-gamma' for non sandox
WHERE			URL										REGION
North America	https://agcod-v2-gamma.amazon.com		us-east-1
				https://agcod-v2.amazon.com
(US, CA, MX)
Europe and Asia	https://agcod-v2-eu-gamma.amazon.com	eu-west-1
				https://agcod-v2-eu.amazon.com
(IT, ES, DE, FR, UK, TR, UAE, KSA, PL, NL, SE)
Far East		https://agcod-v2-fe-gamma.amazon.com	us-west-2
				https://agcod-v2-fe.amazon.com
(JP, AU, SG)

CURRENCY
USD for US
EUR for EU
JPY for JP
CAD for CA
AUD for AU
TRY for TR
AED for UAE

*/

// run info test (prints ENV vars)
$run_info_test = false;
// run test to get funds info
$run_fund_test = true;
// run the normal get/cancel gift card tests
$run_gift_tests = true;
// run mock error check tests
$run_mocks = true;

// should we print debug info
$debug_print = false;
// how long to wait between each call
$debug_wait = 2;
// if set to true will print all the debug logs too
$mock_debug = false;
// wait in seconds between mock tests
$mock_wait = 2;

if ($run_info_test === true) {
	$aws = new Amazon\AmazonIncentives();
	print "checkMe: <pre>" . print_r($aws->checkMe(), true) . "</pre>";
	fwrite($fp, writeLog($aws->checkMe()));
	print "<hr>";
}

$fp = fopen('log/debug.' . date('YmdHis') . '.log', 'w');

// check balance
if ($run_fund_test === true) {
	try {
		$aws_test = Amazon\AmazonIncentives::make()->getAvailableFunds();
		print "AWS: getAvailableFunds: " .  $aws_test->getStatus() . ": "
			. "Amount: " . $aws_test->getAmount() . ", "
			. "Currency: " . $aws_test->getCurrency() . ", "
			. "Timestamp: " . $aws_test->getTimestamp();
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (Exception $e) {
		$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: getAvailableFunds: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'] . ": ";
		if ($debug_print === true) {
			print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
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
	try {
		// we must be sure we pass FLOAT there
		$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value);
		$creation_request_id = $aws_test->getCreationRequestId();
		$gift_card_id = $aws_test->getId();
		$claim_code = $aws_test->getClaimCode();
		$request_status = $aws_test->getStatus();
		print "AWS: buyGiftCard: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: buyGiftCard: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'] . ": ";
		if ($debug_print === true) {
			print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);
	try {
		// cancel above created card card
		$aws_test = Amazon\AmazonIncentives::make()->cancelGiftCard($creation_request_id, $gift_card_id);
		$request_status = $aws_test->getStatus();
		print "AWS: cancelGiftCard: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id;
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: cancelGiftCard: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'] . ": ";
		if ($debug_print === true) {
			print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);

	// set same request ID twice to get same response test
	try {
		$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value);
		$creation_request_id = $aws_test->getCreationRequestId();
		$gift_card_id = $aws_test->getId();
		$claim_code = $aws_test->getClaimCode();
		$request_status = $aws_test->getStatus();
		print "AWS: buyGiftCard: CODE A: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: cancelGiftCard: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'] . ": ";
		if ($debug_print === true) {
			print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	sleep($debug_wait);
	try {
		$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value, $creation_request_id);
		$request_status = $aws_test->getStatus();
		print "AWS: buyGiftCard: SAME CODE A AGAIN: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "CLAIM CODE: <b>" . $claim_code . "</b>";
		if ($debug_print === true) {
			print "<pre>" . print_r($aws_test, true) . "</pre>";
		}
		fwrite($fp, writeLog((array)$aws_test));
	} catch (\Exception $e) {
		$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
		print "AWS: cancelGiftCard: " . $error['status']
			. " [" . $e->getCode() . "]: "
			. $error['code'] . " | " . $error['type']
			. " | " . $error['message'] . ": ";
		if ($debug_print === true) {
			print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		}
		fwrite($fp, writeLog($error));
	}
	print "<br>";
	print "<hr>";
	sleep($debug_wait);
}

// MOCK TEST
if ($mock_debug === true) {
	$mock_ok = '<span style="color:green;">MOCK OK</span>';
	$mock_failure = '<span style="color:red;">MOCK FAILURE</span>';
	$mock_value = 500;

	$mock['F0000'] = [ 'ret' => '', 'st' => 'SUCCESS']; // success mock
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
			$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$mock_value, $creation_id);
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
			$error = Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage());
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
				print "/<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
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
