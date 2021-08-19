<?php

namespace App\MessageHandler;

use App\Message\ResetPasswordNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

class ResetPasswordNotificationHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public function __invoke(ResetPasswordNotification $resetPasswordNotification)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('0b75b45c48-8c20e6@inbox.mailtrap.io', 'mailtrap'))
            ->to($resetPasswordNotification->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetPasswordNotification->getResetPasswordToken(),
            ]);
        $this->mailer->send($email);

    }
}