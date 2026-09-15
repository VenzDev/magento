<?php

declare(strict_types=1);

namespace Training\Greeting\Controller\Adminhtml\Greeting;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Training\Greeting\Api\GreetingRepositoryInterface;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Training_Greeting::greeting_delete';

    public function __construct(
        Context $context,
        private readonly GreetingRepositoryInterface $greetingRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__("We can't find a greeting to delete."));

            return $resultRedirect->setPath('*/*/');
        }

        // TODO:
        // 1. Wywołaj $this->greetingRepository->deleteById($id) w try/catch.
        // 2. Sukces: $this->messageManager->addSuccessMessage(__('You deleted the greeting.'));
        // 3. Błąd (np. CouldNotDeleteException): addErrorMessage z treścią wyjątku
        //    i przekieruj z powrotem na edit tego ID zamiast na listę —
        //    ($resultRedirect->setPath('*/*/edit', ['id' => $id])) — żeby
        //    użytkownik zobaczył błąd w kontekście rekordu, którego dotyczył.

        return $resultRedirect->setPath('*/*/');
    }
}
