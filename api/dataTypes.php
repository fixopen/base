<?php

class dataTypes extends Model
{

    private static $tables = FALSE;

    public static function GetIdByName($name)
    {
        $result = FALSE;
        if (!self::$tables) {
            self::$tables = dataTypes::CustomSelect('');
        }
        foreach (self::$tables as $table) {
            if ($table->name == $name) {
                $result = $table->id;
                break;
            }
        }
        return $result;
    }
}
