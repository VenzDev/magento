<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Training\AccountConfirmation\Model\ConfirmationRequestedAtUpdater;

class RefreshConfirmationRequestedAtOnResendPlugin
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ConfirmationRequestedAtUpdater $confirmationRequestedAtUpdater
    ) {
    }

    /**
     * AccountManagement::resendConfirmation() (wywoływana z Controller\Account\Confirmation —
     * strona "Nie otrzymałeś maila aktywacyjnego?" / login z nieaktywnym kontem) NIE generuje
     * nowego klucza aktywacyjnego, tylko wysyła ponownie mail z tym samym $customer->getConfirmation().
     * Bez tego pluginu nowo wysłany link byłby i tak odrzucony przez ExpireConfirmationKeyPlugin,
     * jeśli od PIERWOTNEJ rejestracji minęło już 24h — Klient nie miałby żadnego sposobu na
     * aktywację konta po tym czasie, mimo poprawnego użycia funkcji "wyślij ponownie".
     *
     * Odświeżamy więc confirmation_requested_at na "teraz" po każdym udanym resendzie, żeby nowy
     * mail dostał pełne 24h ważności.
     */
    public function afterResendConfirmation(AccountManagementInterface $subject, $result, $email, $websiteId = null, $redirectUrl = '')
    {
        if ($result) {
            try {
                $this->confirmationRequestedAtUpdater->touch($this->customerRepository->get($email, $websiteId));
            } catch (NoSuchEntityException $e) {
                // Klient zniknął między resendConfirmation() a tym miejscem — nie ma czego odświeżać.
            }
        }

        return $result;
    }
}
