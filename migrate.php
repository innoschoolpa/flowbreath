<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Commands\MigrateCommand;

$command = new MigrateCommand();
$command->run(); 