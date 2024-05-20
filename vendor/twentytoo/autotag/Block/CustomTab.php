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
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
            $connection = $resource->getConnection();
    
            // Get current product ID
            $productId = $this->getRequest()->getParam('id'); // Assuming you're getting product ID from request parameter
    
            if (!$productId) {
                throw new \Exception('Product ID is not set in the request.');
            }
    
            $select = $connection->select()->from($this->_tableName)
                ->where('order_id = :order_id'); // Correct if order_id is used for product_id
    
            $binds = [':order_id' => $productId];
    
            $results = $connection->fetchAll($select, $binds);
            $this->logger->info("Result data ------> " . json_encode($results));
            
            // Check if the query returned any results
            if (empty($results)) {
                $this->logger->warning('No tags found for product ID:', ['product_id' => $productId]);
                return []; // Or return some default value or handle as necessary
            }
    
            $englishTags = json_decode($results[0]['english_tags'], true);
            $arabicTags = json_decode($results[0]['arabic_tags'], true);
    
            // Determine the current store's locale code
            $locale = $this->_storeManager->getStore()->getLocaleCode();
            $this->logger->info('Locale code detected:', ['locale' => $locale]);
    
            // Check if the locale indicates Arabic
            $isArabic = (strpos($locale, 'ar_') === 0);
    
            // Determine which set of tags to use based on the detected language
            $selectedTags = $isArabic ? $arabicTags : $englishTags;
    
            // Log the selected tags and product ID
            $this->logger->info('Selected tags: ' . json_encode($selectedTags));
            $this->logger->info('Product ID: ' . $productId);
    
            return $selectedTags;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching custom data:', ['exception' => $e->getMessage()]);
            return [];
        }
    }
}
