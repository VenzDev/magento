<?php

declare(strict_types=1);

namespace Training\Greeting\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Training\Greeting\Api\Data\GreetingInterface;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;

class Greeting extends AbstractModel implements GreetingInterface, IdentityInterface
{
    public const CACHE_TAG = 'training_greeting';

    protected function _construct(): void
    {
        $this->_init(GreetingResource::class);
    }

    /**
     * Po każdym save()/delete() Magento woła event clean_cache_by_tags, a
     * Magento\PageCache\Observer\FlushCacheByTags czyści z FPC strony oznaczone
     * TYMI tagami. Samo $_cacheTag nie wystarczy — strategia
     * Tag\Strategy\Identifier bierze tagi wyłącznie z getIdentities().
     *
     * Tag listy jest konieczny: nowy wpis ma świeże ID, którego nie ma jeszcze
     * na żadnej scache'owanej stronie, więc sam tag per-wpis go nie unieważni.
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
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

    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }

    public function setProductId(int $productId): self
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
