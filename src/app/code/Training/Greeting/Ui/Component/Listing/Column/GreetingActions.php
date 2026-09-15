<?php

declare(strict_types=1);

namespace Training\Greeting\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class GreetingActions extends Column
{
    private const URL_PATH_EDIT = 'training_greeting/greeting/edit';
    private const URL_PATH_DELETE = 'training_greeting/greeting/delete';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $name = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            $itemId = $item['entity_id'];

            $item[$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $itemId]),
                'label' => __('Edit'),
            ];

            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $itemId]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete "%1"', $item['message'] ?? ''),
                    'message' => __('Are you sure you want to delete this greeting?'),
                ],
                'post' => true,
            ];
        }

        return $dataSource;
    }
}
