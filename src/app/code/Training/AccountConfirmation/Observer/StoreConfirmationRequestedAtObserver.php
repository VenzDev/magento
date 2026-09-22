<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Training\AccountConfirmation\Setup\Patch\Data\AddConfirmationRequestedAtAttribute;

class StoreConfirmationRequestedAtObserver implements ObserverInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Event `customer_register_success` jest wywoływany z Controller\Account\CreatePost::execute()
     * zaraz po AccountManagement::createAccount() — klient w evencie jest już zapisany w bazie,
     * `customer` to `\Magento\Customer\Api\Data\CustomerInterface`.
     *
     * TODO: jeśli rejestracja wymaga potwierdzenia maila (customer->getConfirmation() !== null —
     * AccountManagement ustawia tam losowy klucz aktywacyjny, gdy customer/create_account/confirm
     * jest włączone), zapisz bieżący czas (np. $this->dateTime->gmtDate()) do atrybutu
     * AddConfirmationRequestedAtAttribute::ATTRIBUTE_CODE przez setCustomAttribute() i odeślij
     * klienta przez $this->customerRepository->save($customer). To znacznik, od którego
     * ExpireConfirmationKeyPlugin liczy 24h ważności linku aktywacyjnego.
     *
     * Uwaga: ten event dotyczy tylko rejestracji ze storefrontu (CreatePost) — rejestracja przez
     * admin/REST/GraphQL go nie wywołuje.
     */
    public function execute(Observer $observer): void
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getData('customer');

        // TODO: dopisz logikę opisaną powyżej
    }
}
