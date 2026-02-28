<?php

namespace App\Models;

use App\Config\Database;

class Settings
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT key, value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    public static function get($key, $default = null)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public static function update($key, $value)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE key = ?");
        $stmt->execute([$value, $key]);
        
        // Эгер жаңыртылган сап жок болсо (0), анда жаңыдан кошуп коёлу
        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }

    public static function updateMany($data)
    {
        foreach ($data as $key => $value) {
            self::update($key, $value);
        }
    }
}
