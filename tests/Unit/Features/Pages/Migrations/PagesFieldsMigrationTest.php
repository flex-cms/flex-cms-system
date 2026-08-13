<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Migrations;

use Flex\Features\Pages\Support\PagesTables;
use PHPUnit\Framework\TestCase;

final class PagesFieldsMigrationTest extends TestCase
{
    public function testMigrationDeclaresPagesFieldsSchemaAndConstraints(): void
    {
        $path = dirname(__DIR__, 5)
            . '/app/Features/Pages/Migrations/20260813010300_create_pages_fields_table.php';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString('PagesTables::fields()', $source);
        self::assertStringContainsString("'field_key'", $source);
        self::assertStringContainsString("'field_group'", $source);
        self::assertStringContainsString("'position'", $source);
        self::assertStringContainsString('pages_fields_page_key_unique', $source);
        self::assertStringContainsString('cascadeOnDelete()', $source);
        self::assertSame('pages_fields', PagesTables::fields());
    }
}
