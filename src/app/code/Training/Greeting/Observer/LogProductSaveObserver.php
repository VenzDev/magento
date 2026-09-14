<?php

declare(strict_types=1);

namespace Training\Greeting\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Training\Greeting\Api\Data\GreetingInterfaceFactory;
use Training\Greeting\Api\GreetingRepositoryInterface;

class LogProductSaveObserver implements ObserverInterface
{
    public function __construct(
        private readonly GreetingRepositoryInterface $greetingRepository,
        private readonly GreetingInterfaceFactory $greetingFactory
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');

        $greeting = $this->greetingFactory->create();
        $greeting->setMessage("Zapisano produkt: {$product->getSku()}")
            ->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
        // product_id nie jest (jeszcze) częścią GreetingInterface — dopisany
        // w Etapie 4 tylko po to, żeby indekser miał po czym grupować. Docelowo
        // "poprawnie" byłoby dodać getProductId()/setProductId() do interfejsu.
        $greeting->setData('product_id', $product->getId());
        $this->greetingRepository->save($greeting);
    }
}
