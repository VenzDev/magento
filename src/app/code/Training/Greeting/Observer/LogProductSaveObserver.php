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

        // TODO:
        // 1. $greeting = $this->greetingFactory->create();
        // 2. $greeting->setMessage("Zapisano produkt: {$product->getSku()}");
        // 3. $greeting->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
        // 4. $this->greetingRepository->save($greeting);
    }
}
