<?php

namespace TwentyToo\TextSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use TwentyToo\TextSearch\Service\ApiService;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

class SearchQueryObserver implements ObserverInterface
{
    protected $logger;
    protected $apiService;
    protected $session;
    protected $registry;
    protected $productFactory;
    protected $storeManager;

    public function __construct(
        LoggerInterface $logger,
        ApiService $apiService,
        SessionManagerInterface $session,
        Registry $registry,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->session = $session;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('SearchQueryObserver: Observer executed.');

        $query = $observer->getControllerAction();
        $queryText = $query->getRequest()->getParam('q');
        $this->logger->info('Search query: ' . $queryText);
        $this->logger->info('Collection: check collection.');
        $collection = $observer->getEvent()->getData('search_result');
        $this->logger->info('Search Result Data: ' . json_encode($searchResult->getData()));
        $collection->clear();
        $this->logger->info('Collection search cleared.');
        $productIds = $this->apiService->getProductIdsFromApi($queryText);
        $this->logger->info('Service Products Observer: ' . json_encode($productIds));

        $this->logger->info('Dynamic Product');
        if (!empty($productIds)) {
            $this->session->setCustomProductIds($productIds);
            $this->session->setSearchQuery($queryText);
            $this->logger->info('Session product IDs and query set.');

            // Register custom data in the registry
            $this->registry->register('custom_data_key', $productIds);
        }
        $staticProducts = [1,1,1];
        $this->logger->info('Static Product');
        foreach ($staticProducts as $productId) {
            $product = $this->productFactory->create()->load($productId);

            if ($product->getId()) {
                $itemData = [
                    'entity_id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'image_url' => $this->getProductImageUrl($product),
                ];
                $this->logger->info('Service Products Observer: ' . json_encode($itemData));
                $item = new \Magento\Framework\DataObject($itemData);
                $collection->addItem($item);
            }
        }
    }

    public function getProductImageUrl($product)
    {
        $imageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $this->logger->info('Product Image URL: ' . $imageUrl);
        return $imageUrl;
    }
 
}
