<?php
namespace App\Controller;

use App\Entity\Notification;
use App\Form\NotificationType;
use App\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class NotificationController extends AbstractController
{

    #[Route('/notifications', name: 'notification_list')]
    public function list(EntityManagerInterface $entityManager, Request $request, MessageBusInterface $bus): Response
    {
        date_default_timezone_set('America/Tijuana');

        $notifications = $entityManager->getRepository(Notification::class)->findAll();

        $notification = new Notification();
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notification);
            $entityManager->flush();

            $now = new \DateTime();
            $scheduledAt = $notification->getScheduledAt();

            $delay = ($scheduledAt->getTimestamp() - $now->getTimestamp()) * 1000;

            if ($delay < 0) {
                throw new \Exception('Scheduled time must be in the future.');
            }

            $bus->dispatch(
                new NotificationMessage($notification->getEmail(), $notification->getMessage()),
                [new DelayStamp($delay)]
            );

            return $this->redirectToRoute('notification_list');
        }

        return $this->render('notification/list.html.twig', [
            'notifications' => $notifications,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/send-test-email', name: 'send_test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('contact@alephlau.com')
            ->to('aleph.lau@outlook.com')
            ->subject('Test Email')
            ->text('This is a test email sent from Symfony.');

        try {
            $mailer->send($email);
            return new Response('Email sent successfully.');
        } catch (\Exception $e) {
            return new Response('Failed to send email: ' . $e->getMessage());
        }
    }
}

