#!/usr/bin/env php
<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$app = require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/Command.php';

/**
 * CLI entry point for the Command utility
 */

$cli = new Command();

if ($argc > 1) {
    $command = implode(' ', array_slice($argv, 1));
    $cli->execute($command);
} else {
    echo "❌ No arguments provided. Type 'php do help' to see available commands.\n";
    exit(1);
}