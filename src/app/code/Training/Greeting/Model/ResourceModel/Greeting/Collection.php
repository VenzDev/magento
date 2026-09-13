<?php

declare(strict_types=1);

namespace Training\Greeting\Model\ResourceModel\Greeting;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Training\Greeting\Model\Greeting as GreetingModel;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(GreetingModel::class, GreetingResource::class);
    }
}
