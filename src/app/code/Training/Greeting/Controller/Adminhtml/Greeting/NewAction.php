<?php

declare(strict_types=1);

namespace Training\Greeting\Controller\Adminhtml\Greeting;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Klasa nie może się nazywać "New" (słowo zarezerwowane w PHP) — Magento
 * mapuje URL .../greeting/new na kontroler o nazwie pliku NewAction.php mimo
 * że w URL jest "new". To po prostu forward do Edit (bez ID = tworzenie
 * nowego rekordu). _forward() nie zwraca ResultInterface, tylko $this — stąd
 * brak return type na execute() i brak `return` (dokładnie jak w core, np.
 * Magento\Cms\Controller\Adminhtml\Block\NewAction).
 */
class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Training_Greeting::greeting';

    public function execute()
    {
        $this->_forward('edit');
    }
}
