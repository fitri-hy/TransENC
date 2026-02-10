<?php

namespace TransENC\Exceptions;

use Exception;

class DecryptionException extends Exception
{
    protected $message = "Failed to decrypt payload.";
}
