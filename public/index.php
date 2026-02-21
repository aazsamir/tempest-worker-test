<?php

declare(strict_types=1);

use Tempest\Router\HttpApplication;
use Tempest\Router\WorkerApplication;

require_once __DIR__ . '/../vendor/autoload.php';

$frankenphpWorker = (bool) ($_ENV['FRANKENPHP_WORKER'] ?? false);

if ($frankenphpWorker) {
    WorkerApplication::boot(
        root: __DIR__ . '/../',
        maxLoops: 500,
    )->run();

    exit();
}

HttpApplication::boot(
    root: __DIR__ . '/../',
)->run();
