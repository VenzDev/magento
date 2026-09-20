<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Greeting extends Template
{
    public function __construct(
        Context $context,
        private readonly TimezoneInterface $timezone,
        private readonly ResolverInterface $localeResolver,
        private readonly Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getGreeting(): Phrase
    {
        return __('Hello, Magento!');
    }

    /**
     * Data renderuje przeglądarka (FPC zamroziłby ją w HTML-u), ale wg locale
     * i strefy czasowej sklepu, nie odwiedzającego.
     */
    public function getDateConfig(): string
    {
        return $this->jsonSerializer->serialize([
            'Training_HelloWorld/js/current-date' => [
                'locale' => str_replace('_', '-', $this->localeResolver->getLocale()),
                'timezone' => $this->timezone->getConfigTimezone(),
            ],
        ]);
    }
}
