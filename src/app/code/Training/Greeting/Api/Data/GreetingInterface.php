<?php

declare(strict_types=1);

namespace Training\Greeting\Api\Data;

interface GreetingInterface
{
    public const ENTITY_ID = 'entity_id';
    public const MESSAGE = 'message';
    public const CREATED_AT = 'created_at';

    public function getId(): ?int;

    public function getMessage(): ?string;

    public function setMessage(string $message): self;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(string $createdAt): self;
}
