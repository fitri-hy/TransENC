<?php

namespace TransENC\Exceptions;

use Exception;

class EncryptionException extends Exception
{
    protected $message = "Failed to encrypt payload.";
}
