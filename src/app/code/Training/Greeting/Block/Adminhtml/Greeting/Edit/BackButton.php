<?php

declare(strict_types=1);

namespace Training\Greeting\Block\Adminhtml\Greeting\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'sort_order' => 10,
        ];
    }
}
