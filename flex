#!/usr/bin/env php
<?php

declare(strict_types=1);

use Flex\Core\Console\Application;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$application = new Application(
    projectPath: __DIR__
);

exit($application->run($argv));
