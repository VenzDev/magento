<?php

declare(strict_types=1);

namespace Training\Greeting\Block\Adminhtml\Greeting\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $id = $this->getGreetingId();

        if (!$id) {
            return [];
        }

        return [
            'label' => __('Delete Greeting'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this greeting?'),
                $this->getUrl('*/*/delete', ['id' => $id])
            ),
            'sort_order' => 20,
        ];
    }
}
