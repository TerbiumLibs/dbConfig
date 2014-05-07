<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->add('DbConfig\\', 'tests');

require __DIR__.'/../../../../bootstrap/autoload.php';
$app = require_once __DIR__.'/../../../../bootstrap/start.php';
$app->run();