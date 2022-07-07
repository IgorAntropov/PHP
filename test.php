<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8');

class DB
{
    protected static $instance = null;

    public function __construct() {}
    public function __clone() {}

    public static function instance()
    {
        if (self::$instance === null)
        {
            $opt  = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => TRUE,
            );
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHAR;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $opt);
        }
        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    public static function run($sql, $args = [])
    {
        if (!$args)
        {
            return self::instance()->query($sql);
        }
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}

$get = <<<SQL
SELECT `PROPERTY_440`, `IBLOCK_ELEMENT_ID`
FROM `b_iblock_element_prop_s17`
SQL;

$get = DB::run($get)->fetchAll();

foreach ($get as $item) {
    $data = $item['PROPERTY_440'];
    $id = $item['IBLOCK_ELEMENT_ID'];

    if (!empty($data)) {
        $data = unserialize($data);

        $value_arr = $data['VALUE'];
        $uniq_value_arr = array_unique($value_arr);
        $diff_value_arr = array_diff_key($value_arr, $uniq_value_arr);

        if (!empty($diff_value_arr)) {
            foreach ($diff_value_arr as $index) {
                unset($data['VALUE'][$index]);
                unset($data['DESCRIPTION'][$index]);
                unset($data['ID'][$index]);
            }

            $data = serialize($data);

            $update = <<<SQL
UPDATE `b_iblock_element_prop_s17` 
SET `PROPERTY_440` = :data 
WHERE `IBLOCK_ELEMENT_ID` = :id
SQL;
            DB::run($update, ['data' => $data, 'id' => $id]);
        }
    }
}