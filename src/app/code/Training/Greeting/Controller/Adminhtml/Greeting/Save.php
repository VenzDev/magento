<?php

declare(strict_types=1);

namespace Training\Greeting\Controller\Adminhtml\Greeting;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Training\Greeting\Api\Data\GreetingInterfaceFactory;
use Training\Greeting\Api\GreetingRepositoryInterface;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Training_Greeting::greeting_save';

    public function __construct(
        Context $context,
        private readonly GreetingRepositoryInterface $greetingRepository,
        private readonly GreetingInterfaceFactory $greetingFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        // TODO:
        // 1. Jeśli $data['entity_id'] jest ustawione i niezerowe — pobierz istniejący
        //    greeting: $this->greetingRepository->getById((int) $data['entity_id']).
        //    W przeciwnym razie stwórz nowy: $this->greetingFactory->create() i
        //    ustaw mu createdAt na teraz — (new \DateTime())->format('Y-m-d H:i:s').
        // 2. Ustaw message (i product_id, jeśli podane w $data — pamiętaj, że
        //    product_id to setData(), nie ma go w GreetingInterface, patrz
        //    Observer/LogProductSaveObserver dla przykładu).
        //    WAŻNE: product_id w bazie jest NOT NULL DEFAULT 0 (0 = brak
        //    powiązanego produktu — zob. db_schema.xml i Setup/Patch/Data/
        //    BackfillGreetingProductId.php). Jeśli pole w formularzu jest
        //    puste, ustaw (int) ($data['product_id'] ?? 0) — NIE zostawiaj
        //    null, bo trigger mview przy DELETE/UPDATE tego rekordu rzuci
        //    "Column 'entity_id' cannot be null" (dokładnie ten błąd, który
        //    już raz naprawialiśmy).
        // 3. Zapisz w try/catch:
        //    - sukces: $this->messageManager->addSuccessMessage(__('You saved the greeting.'));
        //      jeśli $data['back'] === 'continue', wróć na edit z ID
        //      ($resultRedirect->setPath('*/*/edit', ['id' => $greeting->getId()])),
        //      inaczej na listę ($resultRedirect->setPath('*/*/')).
        //    - błąd: $this->messageManager->addErrorMessage($e->getMessage());
        //      $this->_getSession()->setFormData($data); wróć na edit z tym samym ID
        //      (albo bez ID, jeśli to był nowy rekord).

        return $resultRedirect->setPath('*/*/');
    }
}
