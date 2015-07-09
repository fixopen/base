<?php

class users extends Model
{
    private static function specWhereItemProcessor($name, $value)
    {
        //
    }

    public static function validEvidence($evidence)
    {
        $result = FALSE;
        $users = users::CustomSelect(' WHERE "login" = ' . self::DatabaseQuote($evidence->login, 'varchar') . ' AND "password" = ' . self::DatabaseQuote($evidence->password, 'varchar'));
        if (count($users) == 1) {
            $result = $users[0];
        }
        return $result;
    }
}
