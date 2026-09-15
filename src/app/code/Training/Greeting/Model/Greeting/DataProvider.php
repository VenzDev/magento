<?php

declare(strict_types=1);

namespace Training\Greeting\Model\Greeting;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Training\Greeting\Model\ResourceModel\Greeting\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        foreach ($this->collection->getItems() as $greeting) {
            $this->loadedData[$greeting->getId()] = $greeting->getData();
        }

        return $this->loadedData;
    }
}
