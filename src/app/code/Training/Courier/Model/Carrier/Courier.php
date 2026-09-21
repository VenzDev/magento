<?php

declare(strict_types=1);

namespace Training\Courier\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Własny przewoźnik. Kod "trainingcourier" musi się zgadzać z nazwą węzła w
 * etc/config.xml i z id grupy w etc/adminhtml/system.xml — po nim Magento
 * odnajduje konfigurację (carriers/trainingcourier/*) i tę klasę
 * (carriers/trainingcourier/model).
 */
class Courier extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'trainingcourier';

    protected $_isFixed = true;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        private readonly ResultFactory $rateResultFactory,
        private readonly MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Magento woła to dla każdego aktywnego przewoźnika, gdy klient wybiera
     * dostawę. Zwrócenie false znaczy "ten przewoźnik nie oferuje tu żadnej
     * dostawy" — dopóki metoda tak działa, Training Courier nie pojawia się w
     * checkoucie.
     *
     * TODO (Etap 16):
     * 1. Jeśli przewoźnik jest wyłączony w configu (getConfigFlag('active')),
     *    zwróć false.
     * 2. Utwórz wynik przez $this->rateResultFactory->create() — to "pudełko"
     *    na oferowane metody dostawy.
     * 3. Utwórz metodę przez $this->rateMethodFactory->create() i ustaw jej:
     *    carrier (kod przewoźnika), carrier_title (getConfigData('title')),
     *    method (kod metody), method_title (getConfigData('name')).
     * 4. Wylicz cenę. Domyślnie to getConfigData('price'), ale gdy wartość
     *    koszyka ($request->getPackageValueWithDiscount()) jest równa lub
     *    większa od getConfigData('free_shipping_subtotal'), dostawa ma być
     *    darmowa (0). Pusty próg oznacza "nigdy darmowa". Ustaw setPrice() i
     *    setCost() na tę samą wartość.
     * 5. Dołącz metodę do wyniku ($result->append(...)) i zwróć wynik.
     *
     * @param RateRequest $request
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
