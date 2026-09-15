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

        $isNew = empty($data['entity_id']);

        try {
            if ($isNew) {
                $greeting = $this->greetingFactory->create();
                $greeting->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
            } else {
                $greeting = $this->greetingRepository->getById((int) $data['entity_id']);
            }

            $greeting->setMessage($data['message'] ?? '');
            $greeting->setProductId((int) ($data['product_id'] ?? 0));

            $this->greetingRepository->save($greeting);

            $this->messageManager->addSuccessMessage(__('You saved the greeting.'));

            if (($data['back'] ?? null) === 'continue') {
                return $resultRedirect->setPath('*/*/edit', ['id' => $greeting->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['id' => $data['entity_id'] ?? null]);
        }
    }
}
