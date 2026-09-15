<?php

declare(strict_types=1);

namespace Training\Greeting\Controller\Adminhtml\Greeting;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Training_Greeting::greeting';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Training_Greeting::greeting');
        $resultPage->getConfig()->getTitle()->prepend(__('Greetings'));

        return $resultPage;
    }
}
