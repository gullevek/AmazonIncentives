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
print "AWS: buyGiftCard: <pre>" . print_r($aws_test, true) . "</pre><br>";
$creation_request_id = $aws_test->getCreationRequestId();
$gift_card_id = $aws_test->getId();
$claim_code = $aws_test->getClaimCode();
print "AWS creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . "<br>";
print "AWS CLAIM CODE: <b>" . $claim_code . "</b><br>";
print "<hr>";

// cancel card
$aws_test = Amazon\AmazonIncentives::make()->cancelGiftCard($creation_request_id, $gift_card_id);
print "AWS: buyGiftCard: <pre>" . print_r($aws_test, true) . "</pre><br>";
print "<hr>";
 */

// MOCK TEST
$value = 500;
$creation_id = 'F0000';
$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value, $creation_id);
$creation_request_id = $aws_test->getCreationRequestId();
$gift_card_id = $aws_test->getId();
$claim_code = $aws_test->getClaimCode();
print "AWS: MOCK: " . $creation_id . ": buyGiftCard: <pre>" . print_r($aws_test, true) . "</pre><br>";
print "AWS creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . "<br>";
print "AWS CLAIM CODE: <b>" . $claim_code . "</b><br>";
print "<hr>";

$creation_id = 'F2005';
try {
	$aws_test = Amazon\AmazonIncentives::make()->buyGiftCard((float)$value, $creation_id);
	$creation_request_id = $aws_test->getCreationRequestId();
	$gift_card_id = $aws_test->getId();
	$claim_code = $aws_test->getClaimCode();
	print "AWS: MOCK: " . $creation_id . ": buyGiftCard: <pre>" . print_r($aws_test, true) . "</pre><br>";
	print "AWS creationRequestId: " . $creation_request_id . ", gcId: " . $gift_card_id . "<br>";
	print "AWS CLAIM CODE: <b>" . $claim_code . "</b><br>";
} catch (Exception $e) {
	print "AWS: MOCK: " . $creation_id . ": buyGiftCard: FAILURE [" . $e->getCode() . "]: "
		. "<pre>" . print_r(Amazon\AmazonIncentives::decodeExceptionMessage($e->getMessage()), true) . "</pre><br>";
}
print "<hr>";

// ... should do all possible important mock tests

// failed card (invalid data)
// double card

// __END__
