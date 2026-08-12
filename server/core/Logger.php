<?php

namespace Framework\Core;

enum LogLevel: string
{
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
    case DEBUG = 'DEBUG';
    case CRITICAL = 'CRITICAL';
}

class Logger
{
    protected static $logDirectory = './logs/';
    protected static $loggingEnabled = true;


    public static function Write($message, $level = LogLevel::INFO)
    {
        if (!self::$loggingEnabled) {
            return;
        }

        if (!file_exists(self::$logDirectory)) 
        {
            mkdir(self::$logDirectory, 0777, true);
        }

        $logFile = self::$logDirectory . date('Y-m-d') . '.log';

        $formattedMessage = sprintf(
            "[%s] [%s]: %s%s",
            date("Y-m-d H:i:s"),
            $level->value,
            $message,
            PHP_EOL
        );

        error_log($formattedMessage, 3, $logFile);
    }

    public static function setLogDirectory($directory)
    {
        self::$logDirectory = rtrim($directory, '/') . '/';
    }
    public static function enableLogging()
    {
        self::$loggingEnabled = true;
    }
    public static function disableLogging()
    {
        self::$loggingEnabled = false;
    }
}
