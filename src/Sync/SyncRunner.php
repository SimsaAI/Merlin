<?php
namespace Merlin\Sync;

use Merlin\Db\Database;
use Merlin\Db\DatabaseManager;
use Merlin\Sync\Schema\MySqlSchemaProvider;
use Merlin\Sync\Schema\PostgresSchemaProvider;
use Merlin\Sync\Schema\SchemaProvider;
use Merlin\Sync\Schema\SqliteSchemaProvider;
use RuntimeException;

class SyncRunner
{
    private ModelDiff $diff;
    private CodeGenerator $generator;

    public function __construct(
        private DatabaseManager $dbManager
    ) {
        $this->diff = new ModelDiff();
        $this->generator = new CodeGenerator();
    }

    // -------------------------------------------------------------------------
    //  Public API
    // -------------------------------------------------------------------------

    /**
     * Synchronise a single model file against the database schema.
     *
     * @param string $filePath Absolute path to the model PHP file
     * @param bool   $dryRun   When true the file is NOT written; changes are only calculated
     * @param string $dbRole   Database role to introspect (falls back to default if not registered)
     */
    public function syncModel(string $filePath, bool $dryRun = false, string $dbRole = 'read'): SyncResult
    {
        try {
            // 1. Parse the model file
            $parser = new ModelParser($filePath);
            $parsed = $parser->parse();

            // 2. Resolve the table name by instantiating the model via reflection
            $tableName = $this->resolveTableName($parsed->className);

            // 3. Get the database connection for the requested role
            $db = $this->dbManager->getOrDefault($dbRole);

            // 4. Build schema provider from the connection's driver
            $provider = $this->buildProvider($db);

            // 5. Fetch table schema
            $tableSchema = $provider->getTableSchema($tableName);

            // 6. Calculate diff
            $ops = $this->diff->diff($tableSchema, $parsed);

            if (empty($ops)) {
                return new SyncResult(
                    filePath: $filePath,
                    className: $parsed->className,
                    tableName: $tableName,
                    operations: [],
                    applied: false
                );
            }

            // 7. Apply or return dry-run result
            if (!$dryRun) {
                $newCode = $this->generator->applyDiff($parsed, $ops);
                file_put_contents($filePath, $newCode);
            }

            return new SyncResult(
                filePath: $filePath,
                className: $parsed->className,
                tableName: $tableName,
                operations: $ops,
                applied: !$dryRun
            );

        } catch (\Throwable $e) {
            return new SyncResult(
                filePath: $filePath,
                className: $filePath,
                tableName: '?',
                operations: [],
                applied: false,
                error: $e->getMessage()
            );
        }
    }

    /**
     * Synchronise multiple model files.
     *
     * @param string[] $modelFiles Absolute paths to model PHP files
     * @param bool     $dryRun
     * @param string   $dbRole
     * @return SyncResult[]
     */
    public function syncAll(array $modelFiles, bool $dryRun = false, string $dbRole = 'read'): array
    {
        $results = [];
        foreach ($modelFiles as $file) {
            $results[] = $this->syncModel($file, $dryRun, $dbRole);
        }
        return $results;
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    private function resolveTableName(string $className): string
    {
        $ref = new \ReflectionClass($className);

        // Instantiate without calling constructor so abstract-ish  lightweight classes work
        $instance = $ref->newInstanceWithoutConstructor();

        if (!method_exists($instance, 'source')) {
            throw new RuntimeException(
                "Class {$className} does not have a source() method – is it a Merlin Model?"
            );
        }

        return $instance->source();
    }

    private function buildProvider(Database $db): SchemaProvider
    {
        $pdo = $db->getInternalConnection();

        return match ($db->getDriver()) {
            'mysql' => new MySqlSchemaProvider($pdo),
            'pgsql' => new PostgresSchemaProvider($pdo),
            'sqlite' => new SqliteSchemaProvider($pdo),
            default => throw new RuntimeException(
                "No schema provider available for driver '{$db->getDriver()}'"
            ),
        };
    }
}
