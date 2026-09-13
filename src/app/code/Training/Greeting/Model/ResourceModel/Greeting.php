<?php

declare(strict_types=1);

namespace Training\Greeting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Greeting extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('training_greeting', 'entity_id');
    }
}
