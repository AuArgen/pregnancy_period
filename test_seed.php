<?php
require_once __DIR__ . '/public/index.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    
    echo "--- Периоддорду текшерүү ---\n";
    $periods = $db->query("SELECT * FROM periods")->fetchAll();
    foreach ($periods as $p) {
        echo "Период ID: {$p['id']}, Аты: {$p['name']}\n";
        $stages = $db->query("SELECT * FROM stages WHERE period_id = {$p['id']}")->fetchAll();
        foreach ($stages as $s) {
            echo "  - Этап: {$s['title']}\n";
        }
    }

    echo "\n--- Чеклисттерди текшерүү ---\n";
    $checklists = $db->query("SELECT * FROM checklists")->fetchAll();
    foreach ($checklists as $c) {
        echo "Чеклист: {$c['title']}\n";
        $items = json_decode($c['items'], true);
        foreach ($items as $item) {
            echo "  - [ ] {$item['text']}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
