<?php

class IdGenerator
{
    private static $prefix = 0;
    private static $prevTimestamp = 0;
    private static $seqNo = 0;

    public static function SetPrefix($n)
    {
        return self::$prefix = $n;
    }

    public static function GetNewId()
    {
        //[prefix-16][time-32][seqNo-16]
        $result = (self::$prefix << 48) & 0xFFFF000000000000;
        $now = time();
        if ($now != self::$prevTimestamp) {
            self::$prevTimestamp = $now;
            self::$seqNo = 0;
        }
        $result |= ($now << 16) & 0xFFFFFFFF0000;
        $result |= self::$seqNo & 0xFFFF;
        ++self::$seqNo;
        return $result;
    }

}

