<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Etap 12 (i18n) — TODO:
 * 1. getGreeting(): owiń zwracany string w __('Hello, Magento!') zamiast
 *    zwracać gołego stringa. To właśnie ten helper pozwala Magento
 *    podmienić frazę na tłumaczenie z i18n/<locale>.csv w zależności od
 *    locale aktywnego store view (etc/adminhtml/system.xml > General >
 *    Locale Options, albo `bin/magento config:set general/locale/code`).
 * 2. getCurrentDate(): przepisz na $this->timezone->formatDateTime(...)
 *    zamiast gołego DateTime::format('Y-m-d H:i:s') — dzięki temu format
 *    daty (nazwy miesięcy, kolejność dzień/miesiąc/rok) będzie zależny od
 *    locale, a nie zahardkodowany. Sygnatura:
 *    formatDateTime(DateTime $date, int $dateType = \IntlDateFormatter::MEDIUM,
 *    int $timeType = \IntlDateFormatter::MEDIUM, ?string $locale = null,
 *    ?string $timezone = null).
 */
class Greeting extends Template
{
    public function __construct(
        Context $context,
        private readonly TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getGreeting(): string
    {
        // TODO: patrz punkt 1 w opisie klasy wyżej.
        return 'Hello, Magento!';
    }

    public function getCurrentDate(): string
    {
        // TODO: patrz punkt 2 w opisie klasy wyżej.
        return (new DateTime())->format('Y-m-d H:i:s');
    }
}
