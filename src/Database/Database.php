<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOStatement;
use Throwable;
use InvalidArgumentException;

final class Database
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{host: string, port: int, database: string, username: string, password: string} $config
     */
    public static function connect(array $config): self
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['database'],
        );

        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);

        $pdo->exec("SET time_zone = '+00:00'");

        return new self($pdo);
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<int|string, scalar|null> $params
     */
    public function fetchValue(string $sql, array $params = []): mixed
    {
        $value = $this->run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return list<mixed>
     */
    public function fetchColumn(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return int affected rows
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    /**
     * Raw statement without parameters (DDL, SET ...).
     */
    public function statement(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @template T
     * @param callable(self): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<int|string, scalar|null> $params
     */
    private function run(string $sql, array $params): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(
                is_int($key) ? $key + 1 : ':' . ltrim($key, ':'),
                $value,
                match (true) {
                    is_int($value)  => PDO::PARAM_INT,
                    is_bool($value) => PDO::PARAM_BOOL,
                    $value === null => PDO::PARAM_NULL,
                    default         => PDO::PARAM_STR,
                },
            );
        }

        $statement->execute();

        return $statement;
    }

    /**
     * @param list<array<string, scalar|null>> $rows rows with identical keys in identical order
     */
    public function insertMany(string $table, array $rows, int $chunkSize = 100): void
    {
        if ($rows === []) {
            return;
        }

        $columns = array_keys($rows[0]);
        $columnList = implode(', ', array_map(self::quoteIdentifier(...), $columns));
        $rowPlaceholder = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $params = [];

            foreach ($chunk as $row) {
                if (array_keys($row) !== $columns) {
                    throw new InvalidArgumentException(
                        'All rows must have the same columns in the same order',
                    );
                }

                array_push($params, ...array_values($row));
            }

            $this->execute(
                sprintf(
                    'INSERT INTO %s (%s) VALUES %s',
                    self::quoteIdentifier($table),
                    $columnList,
                    implode(', ', array_fill(0, count($chunk), $rowPlaceholder)),
                ),
                $params,
            );
        }
    }

    private static function quoteIdentifier(string $name): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new InvalidArgumentException("Invalid SQL identifier: {$name}");
        }

        return "`{$name}`";
    }
}
