<?php

declare(strict_types=1);

namespace App\Database;

use RuntimeException;

final class Migrator
{
    private const TABLE = 'migrations';
    private const LOCK_NAME = 'app:migrations';
    private const LOCK_TIMEOUT_SEC = 10;

    public function __construct(
        private readonly Database $db,
        private readonly string $migrationsPath,
    ) {
    }

    /**
     * @return list<string> names of executed migrations
     */
    public function migrate(): array
    {
        $this->acquireLock();

        try {
            $this->ensureMigrationsTable();

            $applied = array_flip($this->applied());
            $executed = [];

            foreach ($this->files() as $name => $file) {
                if (isset($applied[$name])) {
                    continue;
                }

                $this->db->statement($this->read($file));
                $this->db->execute('INSERT INTO ' . self::TABLE . ' (migration) VALUES (?)', [$name]);

                $executed[] = $name;
            }

            return $executed;
        } finally {
            $this->releaseLock();
        }
    }

    public function dropAllTables(): void
    {
        $tables = $this->db->fetchColumn(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'",
        );

        $this->db->statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $this->db->statement(sprintf('DROP TABLE `%s`', str_replace('`', '``', (string) $table)));
            }
        } finally {
            $this->db->statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->statement(
            'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                migration   VARCHAR(255) NOT NULL,
                executed_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_migrations_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
    }

    /**
     * @return list<string>
     */
    private function applied(): array
    {
        /** @var list<string> */
        return $this->db->fetchColumn('SELECT migration FROM ' . self::TABLE . ' ORDER BY id');
    }

    /**
     * @return array<string, string> name => absolute path, sorted by name
     */
    private function files(): array
    {
        $files = glob($this->migrationsPath . '/*.sql') ?: [];
        $result = [];

        foreach ($files as $file) {
            $result[basename($file, '.sql')] = $file;
        }

        ksort($result, SORT_STRING);

        return $result;
    }

    private function read(string $file): string
    {
        $sql = file_get_contents($file);

        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException("Migration file is empty or unreadable: {$file}");
        }

        return $sql;
    }

    private function acquireLock(): void
    {
        $acquired = $this->db->fetchValue('SELECT GET_LOCK(?, ?)', [self::LOCK_NAME, self::LOCK_TIMEOUT_SEC]);

        if ((int) $acquired !== 1) {
            throw new RuntimeException('Could not acquire migration lock: another migration is running');
        }
    }

    private function releaseLock(): void
    {
        $this->db->fetchValue('SELECT RELEASE_LOCK(?)', [self::LOCK_NAME]);
    }
}
