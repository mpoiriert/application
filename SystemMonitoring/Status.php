<?php

namespace Draw\Component\Application\SystemMonitoring;

enum Status: string
{
    case OK = 'OK';
    case ERROR = 'ERROR';
    case UNKNOWN = 'UNKNOWN';
}
