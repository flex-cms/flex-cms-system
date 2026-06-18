<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class EmailTemplateSeeder extends AbstractSeed
{
    public function run(): void
    {
        $sqlFilePath = __DIR__ . '/sql/email-templates.sql';

        if (!file_exists($sqlFilePath)) {
            return;
        }

        try {
            $sql = file_get_contents($sqlFilePath);
            $pdo = $this->getAdapter()->getConnection();
            $pdo->exec($sql);
            
            echo "EmailTemplates са импортирани успешно.\n";
        } catch (\Throwable $e) {
            error_log("Грешка при импортиране: " . $e->getMessage());
        }
    }
}