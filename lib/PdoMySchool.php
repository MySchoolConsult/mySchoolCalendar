<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fwolbring
 * Date: 28.10.13
 * Time: 22:46
 */

class PdoMySchool {
    static private $driver = "mysql";
    static private $host = "localhost";
    static private $dbname = "myschool_db2";
    static private $user = "root";
    static private $password = "";

    public static function getPDO() {
        $pdo = new PDO(self::$driver . ":host=" .self::$host . ";dbname=" . self::$dbname, self::$user, self::$password);

        return $pdo;
    }
}