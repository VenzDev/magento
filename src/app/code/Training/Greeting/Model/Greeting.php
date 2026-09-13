<?php

declare(strict_types=1);

namespace Training\Greeting\Model;

use Magento\Framework\Model\AbstractModel;
use Training\Greeting\Api\Data\GreetingInterface;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;

class Greeting extends AbstractModel implements GreetingInterface
{
    protected function _construct(): void
    {
        $this->_init(GreetingResource::class);
    }

    public function getId(): ?int
    {
        $id = $this->getData(self::ENTITY_ID);

        return $id === null ? null : (int) $id;
    }

    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
    }

    public function setMessage(string $message): self
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
