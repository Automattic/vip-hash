#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
	echo 'Warning: Composer should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}
require __DIR__.'/../src/bootstrap.php';

use automattic\vip\hash\Application;

// run the command application
$application = new Application();
$application->run();
