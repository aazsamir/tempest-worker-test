<?php

declare(strict_types=1);

namespace Aazsamir\TempestWorkerTest;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

class Migrate implements MigratesUp, MigratesDown
{
    public string $name = 'create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary('id')
            ->string('name');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('users');
    }
}