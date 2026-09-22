<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Training\AccountConfirmation\Setup\Patch\Data\AddConfirmationRequestedAtAttribute;

class ConfirmationExpirationChecker
{
    public const CONFIRMATION_EXPIRATION_HOURS = 24;

    public function __construct(
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Brak atrybutu confirmation_requested_at (np. konto sprzed wdrożenia tego modułu)
     * traktujemy jako "nie wygasło" — nie mamy punktu odniesienia w czasie.
     */
    public function isExpired(CustomerInterface $customer): bool
    {
        $requestedAt = $customer->getCustomAttribute(AddConfirmationRequestedAtAttribute::ATTRIBUTE_CODE);
        if ($requestedAt === null || !$requestedAt->getValue()) {
            return false;
        }

        $requestedAtTimestamp = strtotime((string) $requestedAt->getValue());
        if ($requestedAtTimestamp === false) {
            return false;
        }

        $expiresAtTimestamp = $requestedAtTimestamp + self::CONFIRMATION_EXPIRATION_HOURS * 3600;

        return $this->dateTime->gmtTimestamp() > $expiresAtTimestamp;
    }
}
