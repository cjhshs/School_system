<?php
// Simple File-Based Cache
class Cache {
    private static $dir;
    private static $default_ttl = 30; // seconds

    public static function init($dir = null) {
        self::$dir = $dir ?: __DIR__ . '/../cache';
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir, 0755, true);
        }
    }

    public static function get($key) {
        self::init();
        $file = self::$dir . '/' . md5($key) . '.cache';
        if (!file_exists($file)) return null;
        
        $data = unserialize(file_get_contents($file));
        if ($data['expires'] > time()) {
            return $data['value'];
        }
        
        unlink($file);
        return null;
    }

    public static function set($key, $value, $ttl = null) {
        self::init();
        $ttl = $ttl ?: self::$default_ttl;
        $file = self::$dir . '/' . md5($key) . '.cache';
        $data = ['value' => $value, 'expires' => time() + $ttl];
        file_put_contents($file, serialize($data));
    }

    public static function delete($key) {
        self::init();
        $file = self::$dir . '/' . md5($key) . '.cache';
        if (file_exists($file)) unlink($file);
    }

    public static function flush() {
        self::init();
        array_map('unlink', glob(self::$dir . '/*.cache'));
    }
}
