<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class File
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM files ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO files (name, original_name, file_type, file_path) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['original_name'],
            $data['file_type'],
            $data['file_path']
        ]);
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $file = self::find($id);
        if ($file) {
            $fullPath = dirname(__DIR__, 2) . '/public' . $file['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $stmt = $db->prepare("DELETE FROM files WHERE id = ?");
            return $stmt->execute([$id]);
        }
        return false;
    }
}
