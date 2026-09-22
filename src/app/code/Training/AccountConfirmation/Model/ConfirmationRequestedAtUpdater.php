<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Training\AccountConfirmation\Setup\Patch\Data\AddConfirmationRequestedAtAttribute;

/**
 * Znacznik, od którego ConfirmationExpirationChecker liczy 24h ważności linku aktywacyjnego.
 * Musi być odświeżany zarówno przy pierwszej rejestracji, jak i przy każdym ponownym
 * wysłaniu maila aktywacyjnego (AccountManagement::resendConfirmation()) — w przeciwnym razie
 * resend wysyła nowy mail z linkiem, który ExpireConfirmationKeyPlugin i tak odrzuci jako
 * przeterminowany na podstawie starej daty rejestracji.
 */
class ConfirmationRequestedAtUpdater
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly DateTime $dateTime
    ) {
    }

    public function touch(CustomerInterface $customer): void
    {
        $customer->setCustomAttribute(
            AddConfirmationRequestedAtAttribute::ATTRIBUTE_CODE,
            $this->dateTime->gmtDate()
        );

        $this->customerRepository->save($customer);
    }
}
