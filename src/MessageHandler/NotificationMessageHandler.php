<?php
namespace App\MessageHandler;

use App\Entity\Notification;
use App\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationMessageHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(private MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(NotificationMessage $message)
    {
        $emailMessage = (new Email())
            ->from('contact@alephlau.com')
            ->to($message->getEmail())
            ->subject('Notification')
            ->text($message->getMessage());

        try {
            $this->mailer->send($emailMessage);
            $notification = $this->entityManager->getRepository(Notification::class)->find($message->getId());

            if ($notification) {
                $notification->setStatus('sent');
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            $notification = $this->entityManager->getRepository(Notification::class)->find($message->getId());

            if ($notification) {
                $notification->setStatus('failed');
                $this->entityManager->flush();
            }
        }
    }

}


