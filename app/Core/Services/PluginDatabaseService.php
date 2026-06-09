<?php

namespace Flex\Core\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class PluginDatabaseService
{
    public function executeSqlFile(string $pluginSlug, string $sqlFilePath): void
    {
        if (!file_exists($sqlFilePath)) {
            throw new \Exception("SQL файлът не е намерен: {$sqlFilePath}");
        }

        $sqlContent = file_get_contents($sqlFilePath);

        $prefix = $pluginSlug . '_'; 

        $processedSql = str_replace('{prefix}', $prefix, $sqlContent);

        $statements = array_filter(array_map('trim', explode(';', $processedSql)));

        $pdo = Capsule::connection()->getPdo();

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
    }
}
