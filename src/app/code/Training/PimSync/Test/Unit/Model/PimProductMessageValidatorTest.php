<?php

declare(strict_types=1);

namespace Training\PimSync\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Training\PimSync\Model\Data\PimProductMessage;
use Training\PimSync\Model\PimProductMessageValidator;

/**
 * Prawdziwy unit test — zero Magento bootstrap, zero mocków repozytoriów.
 * PimProductMessageValidator nie ma żadnych zależności, więc testujemy go
 * bezpośrednio, budując wiadomości przez zwykłe `new`.
 *
 * Uruchomienie: bin/dev-test-run unit Training/PimSync
 * (albo całościowo: bin/dev-test-run unit)
 */
class PimProductMessageValidatorTest extends TestCase
{
    private PimProductMessageValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PimProductMessageValidator();
    }

    /**
     * TODO: zbuduj poprawną wiadomość (PimProductMessage z realnym sku,
     * dodatnią ceną i dodatnim qty), wywołaj $this->validator->validate($message)
     * i sprawdź, że NIE rzuca wyjątku — np. przez wywołanie bez try/catch
     * (jeśli rzuci, test i tak sfailuje) albo jawne
     * $this->expectNotToPerformAssertions() jeśli wolisz to zaznaczyć wprost.
     */
    public function testValidMessagePassesValidation(): void
    {
    }

    /**
     * TODO: zbuduj wiadomość z pustym sku (setSku('')), sprawdź że
     * validate() rzuca \InvalidArgumentException. Podpowiedź:
     * $this->expectException(\InvalidArgumentException::class);
     * (wywołaj to PRZED wywołaniem validate(), nie po).
     */
    public function testEmptySkuThrowsException(): void
    {
    }

    /**
     * TODO: analogicznie jak wyżej, ale z ujemną ceną (setPrice(-10)).
     * Dodatkowo możesz sprawdzić treść komunikatu przez
     * $this->expectExceptionMessage('...') — zwróć uwagę, że musi dokładnie
     * pasować (albo użyj expectExceptionMessageMatches() z regexem).
     */
    public function testNegativePriceThrowsException(): void
    {
    }

    /**
     * TODO: analogicznie, ale z ujemnym qty (setQty(-5)).
     */
    public function testNegativeQtyThrowsException(): void
    {
    }
}
