<?php

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class ResetPasswordNotification
{

    private $email;
    private $resetPasswordToken;

    public function __construct(string $email,ResetPasswordToken $resetPasswordToken)
    {
        $this->email = $email;
        $this->resetPasswordToken = $resetPasswordToken;

    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getResetPasswordToken():ResetPasswordToken
    {
        return $this->resetPasswordToken;
    }
}