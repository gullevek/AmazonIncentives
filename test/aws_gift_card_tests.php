<?php // phpcs:ignore PSR1.Files.SideEffects

// test for Amazon Gift Card Incentives

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

// run tests
// print "checkMe Static: <pre>" . print_r(Amazon\AmazonIncentives::checkMeStatic(), true) . "</pre>";

$aws = new Amazon\AmazonIncentives();
// $aws->createGiftCard(100);
print "checkMe: <pre>" . print_r($aws->checkMe(), true) . "</pre>";
print "<hr>";

// we should open log file to collect all creationRequestId/gcId
// so we can test and cancel

// check balance
try {
	$aws_test = Amazon\AmazonIncentives::make()->getAvailableFunds();
	print "AWS: getAvailableFunds: <pre>" . print_r($aws_test, true) . "</pre><br>";
} catch (Exception $e) {
	print "AWS: getAvailableFunds FAILURE [" . $e->getCode() . "]: "
		. "<pre>" . print_r(Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage()), true) . "</pre><br>";
	exit;
};
// print "LOG: <pre>" . print_r($aws_test->getLog(), true) . "</pre><br>";
print "<hr>";

// skip early for testing
// exit;

/*
// create card
$value = 1000;
// we must be sure we pass FLOAT there
$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value);
$creation_request_id = $aws_test->getCreationRequestId();
$gift_card_id = $aws_test->getId();
$claim_code = $aws_test->getClaimCode();
print "AWS: buyGiftCard: "
	. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
	. "CLAIM CODE: <b>" . $claim_code . "</b><br>";
// print "<pre>" . print_r($aws_test, true) . "</pre><br>";
print "<hr>";
// cancel card
$aws_test = Amazon\AmazonIncentives::make()->cancelGiftCard($creation_request_id, $gift_card_id);
print "AWS: buyGiftCard: <pre>" . print_r($aws_test, true) . "</pre><br>";
print "<hr>";
 */

// MOCK TEST

$mock_ok = '<span style="color:green;">MOCK OK</span>';
$mock_failure = '<span style="color:red;">MOCK FAILURE</span>';

$mock['F0000'] = [ 'ret' => '', 'st' => 'SUCCESS'];
$mock['F2005'] = [ 'ret' => 'F200', 'st' => 'FAILURE'];
$mock['F2010'] = [ 'ret' => 'F200', 'st' => 'FAILURE'];
$mock['F4000'] = [ 'ret' => 'F400', 'st' => 'RESEND'];
$value = 500;
foreach ($mock as $creation_id => $mock_return) {
	try {
		$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value, $creation_id);
		$creation_request_id = $aws_test->getCreationRequestId();
		$gift_card_id = $aws_test->getId();
		$claim_code = $aws_test->getClaimCode();
		$request_status = $aws_test->getStatus();
		print "AWS: MOCK: " . $creation_id . ": buyGiftCard: " . $request_status . ": "
			. "creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . ", "
			. "CLAIM CODE: <b>" . $claim_code . "</b>: ";
		if ($mock_return['st'] == $request_status) {
			print $mock_ok;
		} else {
			print $mock_failure;
		}
		// print "<pre>" . print_r($aws_test, true) . "</pre>";
		print "<br>";
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
		// print "<pre>" . print_r($error['log'][$error['log_id'] ?? ''] ?? [], true) . "</pre>";
		print "<br>";
	}
}
print "<hr>";

// ... should do all possible important mock tests

// failed card (invalid data)
// double card

// __END__
