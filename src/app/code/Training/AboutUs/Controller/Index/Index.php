<?php

declare(strict_types=1);

namespace Training\AboutUs\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $resultPageFactory
    ) {
    }

    public function execute(): ResultInterface
    {
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('About Us'));
        return $page;
    }
}
