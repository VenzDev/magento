<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Greeting extends Template
{
    private const XML_PATH_GREETING_SUFFIX = 'training_greeting/storefront/greeting_suffix';

    public function __construct(
        Context $context,
        private readonly TimezoneInterface $timezone,
        private readonly ResolverInterface $localeResolver,
        private readonly Json $jsonSerializer,
        private readonly ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getGreeting(): Phrase
    {
        return __('Hello, Magento!');
    }

    /**
     * Etap 15 (Stores/Websites/Store Views).
     *
     * TODO: zwróć wartość configu spod self::XML_PATH_GREETING_SUFFIX,
     * w scope ScopeInterface::SCOPE_STORE, BEZ podawania trzeciego argumentu
     * (kodu store'a) — ScopeConfigInterface::getValue() sam rozpozna aktualny
     * store z bieżącego requestu.
     *
     * Zanim napiszesz kod: przewidź, co zobaczysz po ustawieniu tej wartości
     * na poziomie website vs na poziomie store view, gdy odwiedzisz stronę
     * z drugim store view przez ?___store=<code> — czy wartość store view
     * "wygrywa" z wartością website? Sprawdź to potem empirycznie.
     */
    public function getGreetingSuffix(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GREETING_SUFFIX, ScopeInterface::SCOPE_STORE);
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
