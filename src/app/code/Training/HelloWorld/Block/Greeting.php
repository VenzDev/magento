<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use DateTime;
use IntlDateFormatter;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Greeting extends Template
{
    public function __construct(
        Context $context,
        private readonly TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getGreeting(): Phrase
    {
        return __('Hello, Magento!');
    }

    public function getCurrentDate(): string
    {
        return $this->timezone->formatDateTime(new DateTime(), IntlDateFormatter::MEDIUM);
    }
}
