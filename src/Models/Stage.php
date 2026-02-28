<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Stage
{
    public static function all()
    {
        $db = Database::getInstance();
        $sql = "SELECT s.*, p.name as period_name 
                FROM stages s 
                LEFT JOIN periods p ON s.period_id = p.id 
                ORDER BY s.created_at DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO stages (period_id, title, short_info, youtube_url, whatsapp_number) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['period_id'],
            $data['title'],
            $data['short_info'],
            $data['youtube_url'],
            $data['whatsapp_number']
        ]);
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM stages WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $sql = "UPDATE stages SET period_id = ?, title = ?, short_info = ?, youtube_url = ?, whatsapp_number = ? 
                WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['period_id'],
            $data['title'],
            $data['short_info'],
            $data['youtube_url'],
            $data['whatsapp_number'],
            (int)$id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM stages WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public static function search($query)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.*, p.name as period_name 
                FROM stages s 
                LEFT JOIN periods p ON s.period_id = p.id 
                WHERE s.title ILIKE ? OR s.short_info ILIKE ?
                ORDER BY s.created_at DESC";
        $stmt = $db->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
