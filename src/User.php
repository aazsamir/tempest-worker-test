<?php

declare(strict_types=1);

namespace Aazsamir\TempestWorkerTest;

use Tempest\Database\IsDatabaseModel;

class User
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {}
}
