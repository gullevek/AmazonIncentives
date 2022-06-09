<?php

// composer auto loader
$loader = require '../vendor/autoload.php';
// need to add this or it will not load here
$loader->addPsr4('gullevek\\', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
use gullevek\dotEnv\DotEnv;

print "BASE: " . __DIR__ . "<br>";
print "ORIG: <pre>" . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '.env') . "</pre>";

$status = DotEnv::readEnvFile(__DIR__);

print "STATUS: " . (string)$status . "<br>";
print "ENV: <pre>" . print_r($_ENV, true) . "</pre><br>";

// __END__
