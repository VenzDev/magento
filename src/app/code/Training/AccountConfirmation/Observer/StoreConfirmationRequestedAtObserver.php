<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Training\AccountConfirmation\Model\ConfirmationRequestedAtUpdater;

class StoreConfirmationRequestedAtObserver implements ObserverInterface
{
    public function __construct(
        private readonly ConfirmationRequestedAtUpdater $confirmationRequestedAtUpdater
    ) {
    }

    /**
     * Event `customer_register_success` jest wywoływany z Controller\Account\CreatePost::execute()
     * zaraz po AccountManagement::createAccount() — klient w evencie jest już zapisany w bazie,
     * `customer` to `\Magento\Customer\Api\Data\CustomerInterface`.
     *
     * Jeśli rejestracja wymaga potwierdzenia maila (customer->getConfirmation() !== null —
     * AccountManagement ustawia tam losowy klucz aktywacyjny, gdy customer/create_account/confirm
     * jest włączone), zapisujemy bieżący czas jako punkt odniesienia dla 24h ważności linku.
     *
     * Uwaga: ten event dotyczy tylko rejestracji ze storefrontu (CreatePost) — rejestracja przez
     * admin/REST/GraphQL go nie wywołuje.
     */
    public function execute(Observer $observer): void
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getData('customer');

        if ($customer->getConfirmation() === null) {
            return;
        }

        $this->confirmationRequestedAtUpdater->touch($customer);
    }
}
