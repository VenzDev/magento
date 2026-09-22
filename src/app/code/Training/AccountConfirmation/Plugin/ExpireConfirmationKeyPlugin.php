<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Training\AccountConfirmation\Setup\Patch\Data\AddConfirmationRequestedAtAttribute;

class ExpireConfirmationKeyPlugin
{
    private const CONFIRMATION_EXPIRATION_HOURS = 24;

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Plugin `before` na AccountManagementInterface::activate() — wywoływany z
     * Controller\Account\Confirm::execute(), gdy Klient kliknie link aktywacyjny z maila.
     * `before` nie zmienia wyniku oryginalnej metody, może tylko zablokować jej wykonanie
     * rzucając wyjątek (tu: gdy link jest przeterminowany) albo podmienić argumenty.
     *
     * Confirm::execute() łapie \Magento\Framework\Exception\StateException i renderuje
     * komunikat "This confirmation key is invalid or has expired." — dlatego rzucamy
     * ExpiredException (rozszerza StateException), nie zwykły LocalizedException.
     *
     * @return array{0: string, 1: string}
     */
    public function beforeActivate(AccountManagementInterface $subject, $email, $confirmationKey): array
    {
        $this->assertNotExpired($this->customerRepository->get($email));

        return [$email, $confirmationKey];
    }

    /**
     * Ten sam mechanizm co beforeActivate(), ale dla AccountManagementInterface::activateById()
     * — używanej m.in. przez niektóre przepływy webapi/graphql zamiast activate().
     *
     * @return array{0: int, 1: string}
     */
    public function beforeActivateById(AccountManagementInterface $subject, $customerId, $confirmationKey): array
    {
        $this->assertNotExpired($this->customerRepository->getById($customerId));

        return [$customerId, $confirmationKey];
    }

    /**
     * Rzuca ExpiredException, jeśli od zapisania confirmation_requested_at minęło więcej niż
     * self::CONFIRMATION_EXPIRATION_HOURS godzin. Brak atrybutu (np. konto sprzed wdrożenia
     * tego modułu) traktujemy jako "nie blokuj" — nie mamy punktu odniesienia w czasie.
     */
    private function assertNotExpired(CustomerInterface $customer): void
    {
        $requestedAt = $customer->getCustomAttribute(AddConfirmationRequestedAtAttribute::ATTRIBUTE_CODE);
        if ($requestedAt === null || !$requestedAt->getValue()) {
            return;
        }

        $requestedAtTimestamp = strtotime((string) $requestedAt->getValue());
        if ($requestedAtTimestamp === false) {
            return;
        }

        $expiresAtTimestamp = $requestedAtTimestamp + self::CONFIRMATION_EXPIRATION_HOURS * 3600;

        if ($this->dateTime->gmtTimestamp() > $expiresAtTimestamp) {
            throw new ExpiredException(__('This confirmation key is invalid or has expired.'));
        }
    }
}
