<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\State\ExpiredException;
use Training\AccountConfirmation\Model\ConfirmationExpirationChecker;

class ExpireConfirmationKeyPlugin
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ConfirmationExpirationChecker $expirationChecker
    ) {
    }

    /**
     * Plugin `before` na AccountManagementInterface::activate() — wywoływany z
     * Controller\Account\Confirm::execute(), gdy Klient kliknie link aktywacyjny z maila.
     * `before` nie zmienia wyniku oryginalnej metody, może tylko zablokować jej wykonanie
     * rzucając wyjątek (tu: gdy link jest przeterminowany) albo podmienić argumenty.
     *
     * Confirm::execute() łapie \Magento\Framework\Exception\StateException — dlatego rzucamy
     * ExpiredException (rozszerza StateException). Treść komunikatu widoczną dla Klienta
     * ustawia CustomizeConfirmationMessagesPlugin (na kontrolerze), który sprawdza wygaśnięcie
     * PRZED wywołaniem tej metody — ten plugin jest więc również ostatnią linią obrony,
     * np. dla wywołań przez webapi/graphql, gdzie tamten plugin nie działa.
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

    private function assertNotExpired(CustomerInterface $customer): void
    {
        if ($this->expirationChecker->isExpired($customer)) {
            throw new ExpiredException(__('This confirmation key is invalid or has expired.'));
        }
    }
}
