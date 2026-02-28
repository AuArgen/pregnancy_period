<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Checklist
{
    public static function all()
    {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM checklists WHERE is_active = TRUE ORDER BY id DESC")->fetchAll();
    }

    public static function create($title, $items = [])
    {
        $db = Database::getInstance();
        $itemsJson = json_encode($items);
        $stmt = $db->prepare("INSERT INTO checklists (title, items) VALUES (?, ?)");
        return $stmt->execute([$title, $itemsJson]);
    }

    public static function update($id, $title, $items, $isActive = true)
    {
        $db = Database::getInstance();
        $itemsJson = json_encode($items);
        $stmt = $db->prepare("UPDATE checklists SET title = ?, items = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([$title, $itemsJson, (bool)$isActive, (int)$id]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM checklists WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM checklists WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }
}
