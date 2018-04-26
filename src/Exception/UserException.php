<?php

namespace App\Exception;

use Exception;

class UserException extends Exception
{
    public const BAD_PSEUDO = 100;
    public const BAD_PASSWORD = 101;

    public const USER_NO_VALIDATED = 110;
    public const MAIL_NO_VALIDATED = 111;

    public const PSEUDO_EXIST = 120;
    public const MAIL_EXIST = 121;

    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}
