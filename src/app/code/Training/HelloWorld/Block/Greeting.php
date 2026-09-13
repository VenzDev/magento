<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use Magento\Framework\View\Element\Template;

class Greeting extends Template
{
    /**
     * TODO: zwróć tekst powitania, np. "Hello, Magento!".
     */
    public function getGreeting(): string
    {
    }

    /**
     * TODO: zwróć aktualną datę/czas jako sformatowany string (np. przez
     * \DateTime lub \Magento\Framework\Stdlib\DateTime\DateTime wstrzyknięty
     * przez konstruktor).
     */
    public function getCurrentDate(): string
    {
    }
}
