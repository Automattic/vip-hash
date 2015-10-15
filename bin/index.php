<?php

require __DIR__.'/../src/bootstrap.php';

use automattic\vip\hash\rest\SilexApplication;

global $dbdir;

// run the command application
$application = new SilexApplication( $dbdir );
$application->run();
