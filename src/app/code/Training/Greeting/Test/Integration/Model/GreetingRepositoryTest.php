<?php

declare(strict_types=1);

namespace Training\Greeting\Test\Integration\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Training\Greeting\Api\Data\GreetingInterfaceFactory;
use Training\Greeting\Api\GreetingRepositoryInterface;

/**
 * Integration test — działa na PRAWDZIWEJ bazie (magento_integration_tests),
 * przez prawdziwy ObjectManager, zero mocków. Testuje pełny cykl CRUD
 * dokładnie tak, jak testowaliśmy to ręcznie w Etapie 2.
 *
 * #[DbIsolation(true)] owija każdy test w transakcję z automatycznym
 * rollbackiem po teście — nie musisz ręcznie sprzątać zapisanych rekordów.
 *
 * Uruchomienie: bin/dev-test-run integration --filter GreetingRepositoryTest
 * (wymaga wcześniej bin/setup-integration-tests — już zrobione)
 */
#[DbIsolation(true)]
class GreetingRepositoryTest extends TestCase
{
    private GreetingRepositoryInterface $repository;
    private GreetingInterfaceFactory $greetingFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->repository = $objectManager->get(GreetingRepositoryInterface::class);
        $this->greetingFactory = $objectManager->get(GreetingInterfaceFactory::class);
    }

    /**
     * TODO:
     * 1. Stwórz nowy Greeting: $this->greetingFactory->create(), ustaw
     *    setMessage('...') i setCreatedAt((new \DateTime())->format('Y-m-d H:i:s')).
     * 2. Zapisz: $saved = $this->repository->save($greeting).
     * 3. $this->assertNotNull($saved->getId()) — sprawdź, że dostał ID z bazy.
     */
    public function testSaveCreatesNewGreeting(): void
    {
        $greeting = $this->greetingFactory->create();
        $greeting->setMessage('Hello');
        $greeting->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));

        $saved = $this->repository->save($greeting);
        $this->assertNotNull($saved->getId());
    }

    /**
     * TODO: zapisz greeting (jak wyżej), odczytaj go PONOWNIE przez
     * $this->repository->getById($saved->getId()) i sprawdź
     * $this->assertEquals($greeting->getMessage(), $fetched->getMessage()) —
     * to potwierdza, że dane faktycznie wróciły z bazy, nie z pamięci.
     */
    public function testGetByIdReturnsSavedGreeting(): void
    {
        $greeting = $this->greetingFactory->create();
        $greeting->setMessage('Hello');
        $greeting->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));

        $saved = $this->repository->save($greeting);
        $fetched = $this->repository->getById($saved->getId());
        $this->assertEquals($greeting->getMessage(), $fetched->getMessage());
    }

    /**
     * TODO: wywołaj getById() z ID, które na pewno nie istnieje (np. 999999999)
     * wewnątrz $this->expectException(NoSuchEntityException::class) — ustaw
     * expectException PRZED wywołaniem, nie po.
     */
    public function testGetByIdThrowsForMissingGreeting(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById(999999999);
    }

    /**
     * TODO: zapisz greeting, usuń go przez $this->repository->deleteById(),
     * sprawdź że kolejne getById() na tym samym ID rzuca
     * NoSuchEntityException — dokładnie ten sam scenariusz, który
     * sprawdzaliśmy ręcznie skryptem bootstrapującym w Etapie 2.
     */
    public function testDeleteByIdRemovesGreeting(): void
    {
        $greeting = $this->greetingFactory->create();
        $greeting->setMessage('Hello');
        $greeting->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));

        $saved = $this->repository->save($greeting);
        $this->repository->deleteById($saved->getId());
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($saved->getId());
    }
}
