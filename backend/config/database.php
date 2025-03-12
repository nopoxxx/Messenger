<?php

class Database
{
    private static $host = 'db';
    private static $dbname = 'messenger';
    private static $user = 'user';
    private static $password = 'password';
    private static $pdo;

    public static function connect()
    {
        if (!self::$pdo) {
            self::$pdo = new PDO(
                'mysql:host=' . self::$host . ';dbname=' . self::$dbname,
                self::$user,
                self::$password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        return self::$pdo;
    }
}
