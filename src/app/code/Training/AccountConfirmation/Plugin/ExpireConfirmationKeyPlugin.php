<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Training\AccountConfirmation\Setup\Patch\Data\AddConfirmationRequestedAtAttribute;

class ExpireConfirmationKeyPlugin
{
    private const CONFIRMATION_EXPIRATION_HOURS = 24;

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Plugin `before` na AccountManagementInterface::activate() — wywoływany z
     * Controller\Account\Confirm::execute(), gdy Klient kliknie link aktywacyjny z maila.
     * `before` nie zmienia wyniku oryginalnej metody, może tylko zablokować jej wykonanie
     * rzucając wyjątek (tu: gdy link jest przeterminowany) albo podmienić argumenty.
     *
     * Confirm::execute() łapie \Magento\Framework\Exception\StateException i renderuje
     * komunikat "This confirmation key is invalid or has expired." — dlatego rzucaj
     * ExpiredException (rozszerza StateException), nie zwykły LocalizedException.
     *
     * TODO:
     *  1. Załaduj klienta po $email: $this->customerRepository->get($email).
     *  2. Odczytaj atrybut AddConfirmationRequestedAtAttribute::ATTRIBUTE_CODE przez
     *     $customer->getCustomAttribute(...)?->getValue().
     *  3. Jeśli atrybut istnieje i minęło więcej niż self::CONFIRMATION_EXPIRATION_HOURS
     *     godzin od tej daty (porównaj przez $this->timezone->date()) — rzuć
     *     new ExpiredException(__('This confirmation key is invalid or has expired.')).
     *  4. Jeśli atrybutu nie ma (np. konto sprzed wdrożenia modułu) — nie blokuj.
     *
     * @return array{0: string, 1: string}
     */
    public function beforeActivate(AccountManagementInterface $subject, $email, $confirmationKey): array
    {
        // TODO: dopisz logikę opisaną powyżej

        return [$email, $confirmationKey];
    }

    /**
     * Ten sam mechanizm co beforeActivate(), ale dla AccountManagementInterface::activateById()
     * — używanej m.in. przez niektóre przepływy webapi/graphql zamiast activate().
     *
     * TODO: ta sama logika co w beforeActivate(), ale ładowanie klienta przez
     * $this->customerRepository->getById($customerId).
     *
     * @return array{0: int, 1: string}
     */
    public function beforeActivateById(AccountManagementInterface $subject, $customerId, $confirmationKey): array
    {
        // TODO: dopisz logikę opisaną powyżej

        return [$customerId, $confirmationKey];
    }
}
