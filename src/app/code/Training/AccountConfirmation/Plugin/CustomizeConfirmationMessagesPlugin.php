<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\Confirm;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Training\AccountConfirmation\Model\ConfirmationExpirationChecker;

class CustomizeConfirmationMessagesPlugin
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ConfirmationExpirationChecker $expirationChecker,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory $redirectFactory,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Confirm::execute() zawsze łapie \Magento\Framework\Exception\StateException i pokazuje
     * ten sam komunikat "This confirmation key is invalid or has expired." przez
     * messageManager->addException($e, $alternativeText) — $alternativeText nadpisuje
     * prawdziwą treść wyjątku bez względu na jego typ. Dotyczy to też przypadku, gdy konto
     * jest już aktywne: AccountManagement::activateCustomer() rzuca wtedy
     * InvalidTransitionException('The account is already active.'), ale Klient i tak widzi
     * generyczny komunikat o nieprawidłowym/wygasłym linku.
     *
     * Ten plugin sprawdza oba przypadki (konto już aktywne, link wygasł) PRZED wywołaniem
     * oryginalnej metody i — tylko dla nich — pokazuje właściwy komunikat i przekierowuje
     * samodzielnie, pomijając $proceed(). Dla pozostałych przypadków (poprawna aktywacja,
     * zły klucz) oddaje sterowanie natywnej logice bez zmian.
     *
     * @return mixed
     */
    public function aroundExecute(Confirm $subject, callable $proceed)
    {
        $customerId = (int) $subject->getRequest()->getParam('id', 0);
        if (!$customerId) {
            return $proceed();
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return $proceed();
        }

        if ($customer->getConfirmation() === null) {
            $this->messageManager->addSuccessMessage(__('This account is already confirmed.'));

            return $this->redirectFactory->create()->setPath('customer/account/login');
        }

        if ($this->expirationChecker->isExpired($customer)) {
            $this->messageManager->addErrorMessage(
                __('The confirmation link has expired. Please request a new one.')
            );

            return $this->redirectToResendConfirmation($customer->getEmail());
        }

        return $proceed();
    }

    private function redirectToResendConfirmation(string $email): Redirect
    {
        return $this->redirectFactory->create()->setUrl(
            $this->urlBuilder->getUrl('customer/account/confirmation', ['_query' => ['email' => $email]])
        );
    }
}
