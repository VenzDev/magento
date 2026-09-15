<?php

declare(strict_types=1);

namespace Training\Greeting\Api\Data;

interface GreetingInterface
{
    public const ENTITY_ID = 'entity_id';
    public const MESSAGE = 'message';
    public const CREATED_AT = 'created_at';
    public const PRODUCT_ID = 'product_id';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * @return GreetingInterface
     */
    public function setMessage(string $message): GreetingInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @return GreetingInterface
     */
    public function setCreatedAt(string $createdAt): GreetingInterface;

    /**
     * 0 = brak powiązanego produktu (sentinel, zob. db_schema.xml).
     *
     * @return int
     */
    public function getProductId(): int;

    /**
     * @return GreetingInterface
     */
    public function setProductId(int $productId): GreetingInterface;
}
