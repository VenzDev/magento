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
     * Etap 14 (Full Page Cache) — TODO: zwróć tagi tego wpisu.
     *
     * Po każdym save()/delete() Magento woła event clean_cache_by_tags, a
     * Magento\PageCache\Observer\FlushCacheByTags czyści z FPC wszystkie strony
     * oznaczone TYMI tagami. Samo ustawienie protected $_cacheTag NIE wystarczy —
     * strategia Tag\Strategy\Identifier bierze tagi wyłącznie z getIdentities()
     * i zwraca [] dla obiektu, który nie jest IdentityInterface.
     *
     * Zastanów się, jakie tagi ma zwracać wpis, żeby zarówno edycja
     * istniejącego wpisu, jak i dodanie nowego unieważniało stronę z listą
     * (zob. pytanie w Training\HelloWorld\Block\LatestGreetings::getIdentities()).
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [];
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
