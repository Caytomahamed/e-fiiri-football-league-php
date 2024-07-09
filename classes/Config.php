<?php

class Config
{
    public static $servername = "localhost";
    public static $username = "root";
    public static $password = "12345678";
    public static $dbname = "football";
    public static $tableName = "fixtures";
    public static $startDate = '2024-07-7'; // Default start date

    public static function setStartDate($startDate)
    {
        self::$startDate = $startDate;
    }

    public static function getStartDate()
    {
        return self::$startDate;
    }
}
