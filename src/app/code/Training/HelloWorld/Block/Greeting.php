<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use DateTime;
use Magento\Framework\View\Element\Template;

class Greeting extends Template
{
    public function getGreeting(): string
    {
        return 'Hello, Magento!';
    }

    public function getCurrentDate(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }
}
