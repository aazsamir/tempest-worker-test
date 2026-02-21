<?php

declare(strict_types=1);

namespace Aazsamir\TempestWorkerTest;

use Tempest\Database\DatabaseSeeder;
use Tempest\Database\Query;
use UnitEnum;

use function Tempest\Database\query;

class Seed implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        $count = 1000;

        $sql = <<<SQL
        INSERT INTO users (id, name) VALUES
        SQL;
        for ($i = 1; $i <= $count; $i++) {
            $sql .= "({$i}, 'User {$i}'),";
        }
        $sql = rtrim($sql, ',') . ';';
        $query = new Query($sql);
        $query->execute();
    }
}