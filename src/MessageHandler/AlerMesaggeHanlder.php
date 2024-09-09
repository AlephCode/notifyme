<?php

namespace App\MessageHandler;

use App\Message\AlertMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AlertMessageHandler
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(AlertMessage $message)
    {
        $email = (new Email())
            ->from('aleph.lau@outlook.com')
            ->to($message->getEmail())
            ->subject('Alerta Programada')
            ->text($message->getMessage());

        $this->mailer->send($email);
    }
}
