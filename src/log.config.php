<?php

declare(strict_types=1);

use Tempest\Log\Config\SimpleLogConfig;

return new SimpleLogConfig(
    path: 'php://stdout',
);