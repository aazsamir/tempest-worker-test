<?php

declare(strict_types=1);

namespace Aazsamir\TempestWorkerTest;

use Tempest\Console\ConsoleCommand;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\MigrationManager;

class MigrateCommand
{
    public function __construct(
        private MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(name: 'migrate-and-seed')]
    public function __invoke()
    {
        $migrations = [
            new CreateMigrationsTable(),
            new Migrate(),
        ];

        foreach ($migrations as $migration) {
            $this->migrationManager->executeUp($migration);
        }

        (new Seed())->run(null);
    }
}
