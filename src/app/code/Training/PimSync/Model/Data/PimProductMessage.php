<?php

declare(strict_types=1);

namespace Training\PimSync\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Training\PimSync\Api\Data\PimProductMessageInterface;

class PimProductMessage extends AbstractSimpleObject implements PimProductMessageInterface
{
    public function getSku(): string
    {
        return (string) $this->_get(self::SKU);
    }

    public function setSku(string $sku): PimProductMessageInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getName(): string
    {
        return (string) $this->_get(self::NAME);
    }

    public function setName(string $name): PimProductMessageInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getPrice(): float
    {
        return (float) $this->_get(self::PRICE);
    }

    public function setPrice(float $price): PimProductMessageInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getQty(): int
    {
        return (int) $this->_get(self::QTY);
    }

    public function setQty(int $qty): PimProductMessageInterface
    {
        return $this->setData(self::QTY, $qty);
    }

    public function getStatus(): int
    {
        return (int) $this->_get(self::STATUS);
    }

    public function setStatus(int $status): PimProductMessageInterface
    {
        return $this->setData(self::STATUS, $status);
    }
}
