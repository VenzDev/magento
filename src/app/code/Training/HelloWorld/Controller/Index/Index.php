<?php

declare(strict_types=1);

namespace Training\HelloWorld\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        // TODO: zbuduj stronę przez $this->resultPageFactory->create() i ustaw
        // tytuł strony (np. $page->getConfig()->getTitle()->set('...')), po czym
        // zwróć obiekt strony. Layout handle "helloworld_index_index" (z
        // view/frontend/layout/) zostanie automatycznie dołączony po nazwie
        // route/controller/action.
    }
}
