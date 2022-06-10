<?php

// just print out env data nd connect data
// checkMe from AmazonIntentives call is requal to
// run_info_test === true in aws_gift_card_tests.php

$loader = require '../vendor/autoload.php';
// need to add this or it will not load here
$loader->addPsr4('gullevek\\', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
// print "LOADER: <pre>" . print_r($loader, true) . "</pre>";

use gullevek\AmazonIncentives\AmazonIncentives;
use gullevek\dotEnv\DotEnv;

// load env data with dotenv
DotEnv::readEnvFile(__DIR__);

print "_ENV: <pre>" . print_r($_ENV, true) . "</pre>";

$aws = new AmazonIncentives();
print "checkMe: <pre>" . print_r($aws->checkMe(), true) . "</pre>";

// __END__
