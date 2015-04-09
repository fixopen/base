<?php

class DatabaseConnection
{

    public static function GetInstance() {
        if (self::$db == NULL) {
            self::$db = new PDO(self::$driver
                . ':host=' . self::$host
                . ';port=' . self::$port
                . ';dbname=' . self::$dbname
                . ';user=' . self::$username
                . ';password=' . self::$password,
                self::$username,
                self::$password,
                array(PDO::ATTR_PERSISTENT => TRUE,
                    PDO::ATTR_EMULATE_PREPARES => FALSE,
                    PDO::ATTR_STRINGIFY_FETCHES => FALSE));
            //print_r(self::$db);
            self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            self::$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);
            //print_r(self::$db);
        }
        return self::$db;
    }

    public static function InitMySQLCharset() {
        self::GetInstance()->exec("SET CHARACTER SET 'utf8'");
        self::GetInstance()->exec("SET NAMES 'utf8'");
    }

    private static $db = null;
    private static $driver = 'pgsql'; //'mysql'
    private static $port = '5432'; //'3306'
    private static $host = '127.0.0.1';
    private static $dbname = 'base';
    private static $username = 'postgres'; //'root'
    private static $password = '123456';

}