<?php

namespace TwentyToo\AutoTag\Block;

use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;

class CustomTab extends Template
{
    protected $_tableName = 'twentytoo_tags';
    protected $logger;
    protected $productFactory;

    public function __construct(
        Template\Context $context,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    public function getCustomData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        // Get current product ID
        $productId = $this->getRequest()->getParam('id'); // Assuming you're getting product ID from request parameter

        $select = $connection->select()->from($this->_tableName)
            ->where('order_id = :order_id');

        // $staticOrderId = 'dress2';
        $binds = [':order_id' => $productId];

        $results = $connection->fetchAll($select, $binds);
        $englishTags = json_decode($results[0]['english_tags'], true);
        $arabicTags = json_decode($results[0]['arabic_tags'], true);
        $allTags = [
            'english_tags' => $englishTags,
            'arabic_tags' => $arabicTags
        ];

        // Get product instance
        $product = $this->productFactory->create()->load($productId);
        
        // Log the results array
        $this->logger->info('Results array:', $allTags);
        $this->logger->info('Product ID:', ['product_id' => $productId]);
        return $allTags;
    }
}