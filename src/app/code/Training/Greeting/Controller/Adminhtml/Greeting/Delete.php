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

        try {
            $this->greetingRepository->deleteById($id);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
