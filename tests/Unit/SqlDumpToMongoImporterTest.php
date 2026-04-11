<?php

namespace Tests\Unit;

use App\Services\SqlDumpToMongoImporter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SqlDumpToMongoImporterTest extends TestCase
{
    public function test_dry_run_import_counts_known_and_unknown_tables(): void
    {
        $sql = <<<'SQL'
        -- Sample export
        INSERT INTO `settings` (`id`, `key`, `value`, `created_at`) VALUES
        (1, 'site_name', 'Khai Tri Edu', '2026-04-01 08:30:00'),
        (2, 'assistant_config', '{"model":"gemini","enabled":true}', '2026-04-01 08:31:00');
        INSERT INTO `users` (`id`, `name`, `email`) VALUES
        (1, 'Dang O\'Connor', 'dang@example.com');
        INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
        (1, '2026_01_01_000000_create_examples_table', 1);
        SQL;

        $path = tempnam(sys_get_temp_dir(), 'sql-dump-');
        $this->assertNotFalse($path);
        file_put_contents($path, $sql);

        try {
            $summary = (new SqlDumpToMongoImporter())->import($path, true);
        } finally {
            @unlink($path);
        }

        $this->assertSame(3, $summary['statements']);
        $this->assertSame(2, $summary['imported']['settings'] ?? 0);
        $this->assertSame(1, $summary['imported']['users'] ?? 0);
        $this->assertSame(1, $summary['unknown_tables']['migrations'] ?? 0);
        $this->assertSame([], $summary['skipped']);
    }

    public function test_parse_rows_keeps_commas_and_escaped_quotes_inside_strings(): void
    {
        $importer = new SqlDumpToMongoImporter();
        $reflection = new ReflectionClass($importer);
        $method = $reflection->getMethod('parseRows');

        $rows = $method->invoke(
            $importer,
            "(1, 'Intro, nâng cao', 'O\\'Connor'),(2, 'JSON payload', '{\"enabled\":true}')"
        );

        $this->assertCount(2, $rows);
        $this->assertSame(["1", "'Intro, nâng cao'", "'O\\'Connor'"], $rows[0]);
        $this->assertSame(["2", "'JSON payload'", "'{\"enabled\":true}'"], $rows[1]);
    }

    public function test_decode_value_normalizes_common_sql_literals(): void
    {
        $importer = new SqlDumpToMongoImporter();
        $reflection = new ReflectionClass($importer);
        $method = $reflection->getMethod('decodeValue');

        $this->assertNull($method->invoke($importer, 'NULL'));
        $this->assertSame(42, $method->invoke($importer, '42'));
        $this->assertSame(12.5, $method->invoke($importer, '12.5'));
        $this->assertNull($method->invoke($importer, "'0000-00-00 00:00:00'"));
        $this->assertSame(['enabled' => true], $method->invoke($importer, '\'{"enabled":true}\''));
        $this->assertSame("Dang O'Connor", $method->invoke($importer, "'Dang O\\'Connor'"));
    }
}
