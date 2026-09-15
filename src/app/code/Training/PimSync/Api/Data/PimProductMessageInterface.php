<?php

declare(strict_types=1);

namespace Training\PimSync\Api\Data;

/**
 * DTO wiadomości publikowanej na topic training.pim.product.sync — reprezentuje
 * "zewnętrzny PIM" mówiący Magento "zaktualizuj/utwórz ten produkt".
 *
 * Jawne @return (nie `self`) na każdej metodzie — dokładnie z tego samego
 * powodu co przy GreetingInterface w Etapie 6: mechanizm MessageQueue serializuje
 * i deserializuje wiadomości przez refleksję (Magento\Framework\Communication\Config\
 * ReflectionGenerator), która ma te same wymagania co webapi.
 */
interface PimProductMessageInterface
{
    public const SKU = 'sku';
    public const NAME = 'name';
    public const PRICE = 'price';
    public const QTY = 'qty';
    public const STATUS = 'status';

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return PimProductMessageInterface
     */
    public function setSku(string $sku): PimProductMessageInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return PimProductMessageInterface
     */
    public function setName(string $name): PimProductMessageInterface;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @return PimProductMessageInterface
     */
    public function setPrice(float $price): PimProductMessageInterface;

    /**
     * @return int
     */
    public function getQty(): int;

    /**
     * @return PimProductMessageInterface
     */
    public function setQty(int $qty): PimProductMessageInterface;

    /**
     * 1 = enabled, 2 = disabled (te same wartości co
     * \Magento\Catalog\Model\Product\Attribute\Source\Status).
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return PimProductMessageInterface
     */
    public function setStatus(int $status): PimProductMessageInterface;
}
