<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Period
{
    public static function all()
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT p.*, COUNT(s.id) as stages_count 
            FROM periods p 
            LEFT JOIN stages s ON p.id = s.period_id 
            GROUP BY p.id 
            ORDER BY p.sort_order ASC, p.id ASC
        ")->fetchAll();
    }

    public static function create($name, $sortOrder = 0)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO periods (name, sort_order) VALUES (?, ?)");
        return $stmt->execute([$name, (int)$sortOrder]);
    }

    public static function update($id, $name, $sortOrder)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE periods SET name = ?, sort_order = ? WHERE id = ?");
        return $stmt->execute([$name, (int)$sortOrder, (int)$id]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM periods WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM periods WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }
}
