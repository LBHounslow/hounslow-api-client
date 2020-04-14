<?php

namespace Hounslow\ApiClient\Enum;

class MonologEnum
{
    const DEBUG     = 'DEBUG';
    const INFO      = 'INFO';
    const NOTICE    = 'NOTICE';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';
    const CRITICAL  = 'CRITICAL';
    const ALERT     = 'ALERT';
    const EMERGENCY = 'EMERGENCY';

    const LEVELS = [
        self::DEBUG,
        self::INFO,
        self::NOTICE,
        self::WARNING,
        self::ERROR,
        self::CRITICAL,
        self::ALERT,
        self::EMERGENCY
    ];

    const LINK = 'https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels';
}