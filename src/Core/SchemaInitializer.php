<?php

namespace App\Core;

use App\Config\Database;

class SchemaInitializer
{
    public static function init()
    {
        $db = Database::getInstance();

        $queries = [
            "CREATE TABLE IF NOT EXISTS admins (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS periods (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                sort_order INT DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS stages (
                id SERIAL PRIMARY KEY,
                period_id INT REFERENCES periods(id) ON DELETE CASCADE,
                title VARCHAR(255) NOT NULL,
                short_info TEXT,
                youtube_url VARCHAR(255),
                whatsapp_number VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS checklists (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                items JSONB,
                is_active BOOLEAN DEFAULT TRUE
            )",
            "CREATE TABLE IF NOT EXISTS settings (
                id SERIAL PRIMARY KEY,
                key VARCHAR(100) UNIQUE NOT NULL,
                value TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS files (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_type VARCHAR(100) NOT NULL,
                file_path TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }

        self::createDefaultAdmin($db);
        self::seedSettings($db);
        self::seedData($db);
    }

    private static function seedSettings($db)
    {
        $defaultSettings = [
            'hero_title' => 'Кош бойлуулук - бул кереметтүү саякат',
            'hero_description' => 'Ар бир этапта сизди колдойбуз. Пайдалуу маалыматтар, видео-сабактар жана адистердин кеңештери.',
            'hero_image' => 'https://www.bipolar.su/wp-content/uploads/2021/05/beremennost-e1673450643393.jpg'
        ];

        foreach ($defaultSettings as $key => $value) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE key = ?");
            $stmt->execute([$key]);
            if ($stmt->fetchColumn() == 0) {
                $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }
        }
    }

    private static function seedData($db)
    {
        // 1. Периоддор жок болсо, 3 период жана ар бирине 3төн этап кошуу
        $stmt = $db->query("SELECT COUNT(*) FROM periods");
        if ($stmt->fetchColumn() == 0) {
            $periods = [
                ['name' => '1-период (1-13 жума)', 'sort_order' => 1],
                ['name' => '2-период (14-27 жума)', 'sort_order' => 2],
                ['name' => '3-период (28-40 жума)', 'sort_order' => 3],
            ];

            foreach ($periods as $p) {
                $stmt = $db->prepare("INSERT INTO periods (name, sort_order) VALUES (?, ?) RETURNING id");
                $stmt->execute([$p['name'], $p['sort_order']]);
                $periodId = $stmt->fetchColumn();

                if ($periodId) {
                    // Ар бир периодго 3төн этап кошуу
                    for ($i = 1; $i <= 3; $i++) {
                        $stmtStage = $db->prepare("INSERT INTO stages (period_id, title, short_info) VALUES (?, ?, ?)");
                        $stmtStage->execute([
                            $periodId,
                            "{$i}-этап: " . $p['name'],
                            "Бул этаптын маалыматы жакында кошулат. Бул жерде сиздин ден-соолугуңуз жана баланын өсүүсү боюнча маалымат болот."
                        ]);
                    }
                }
            }
        }

        // 2. Чеклисттер жок болсо, демейки чеклист кошуу
        $stmt = $db->query("SELECT COUNT(*) FROM checklists");
        if ($stmt->fetchColumn() == 0) {
            $checklistItems = [
                ['text' => 'Дарыгерге катталуу', 'completed' => false],
                ['text' => 'Витаминдерди ичүүнү баштоо', 'completed' => false],
                ['text' => 'Туура тамактануу режимин түзүү', 'completed' => false]
            ];
            $itemsJson = json_encode($checklistItems);

            $stmt = $db->prepare("INSERT INTO checklists (title, items, is_active) VALUES (?, ?, ?)");
            $stmt->execute(['Кош бойлуулуктун башталышы үчүн чеклист', $itemsJson, true]);
        }
    }

    private static function createDefaultAdmin($db)
    {
        $stmt = $db->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            $username = 'admin';
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $password]);
        }
    }
}
