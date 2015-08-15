<?php

require __DIR__.'/../src/bootstrap.php';

use automattic\vip\hash\rest\SilexApplication;

// run the command application
$application = new SilexApplication();
$application->run();
