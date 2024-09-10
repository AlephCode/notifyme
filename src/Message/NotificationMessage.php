<?php
namespace App\Message;

class NotificationMessage
{
    private int $id;
    private string $email;
    private string $message;

    public function __construct(int $id, string $email, string $message)
    {
        $this->id = $id;
        $this->email = $email;
        $this->message = $message;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
