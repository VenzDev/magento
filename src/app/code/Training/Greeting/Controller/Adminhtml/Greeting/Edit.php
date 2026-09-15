<?php

declare(strict_types=1);

namespace Training\Greeting\Controller\Adminhtml\Greeting;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Training\Greeting\Api\GreetingRepositoryInterface;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Training_Greeting::greeting';

    public function __construct(
        Context $context,
        private readonly \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        private readonly GreetingRepositoryInterface $greetingRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $title = __('New Greeting');

        // Tylko po to, żeby ładnie ustawić tytuł strony / breadcrumb i od razu
        // przekierować, jeśli ktoś wejdzie z nieistniejącym ID. Realne dane
        // dla formularza dostarcza Model\Greeting\DataProvider — niezależnie
        // od tego sprawdzenia.
        if ($id) {
            try {
                $greeting = $this->greetingRepository->getById($id);
                $title = __('Edit Greeting #%1', $greeting->getId());
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This greeting no longer exists.'));

                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Training_Greeting::greeting');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
