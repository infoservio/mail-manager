<?php

namespace endurant\smtpmailer\errors;

use endurant\smtpmailer\models\Log;


class DbSmtpMailerPluginException extends SmtpMailerPluginException
{
    protected $culprit = Log::DB_CULPRIT;
    
    public function __construct(array $errors, string $message, string $method, string $category) {
        parent::__construct($errors, $message, $method, $category);
    }
}