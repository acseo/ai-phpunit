<?php
require __DIR__.'/../vendor/autoload.php';

use ACSEO\AIPHPUnit\ProcessDirectory;
use ACSEO\AIPHPUnit\ProcessFile;
use Symfony\Component\Console\Application;

$application = new Application('AI-PHPUnit by ACSEO', '1.0.0');

$application->add(new ProcessFile());
$application->add(new ProcessDirectory());

$application->run();

