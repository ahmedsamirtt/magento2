<?php

namespace TwentyToo\TextSearch\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use TwentyToo\TextSearch\Service\ApiService;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\DataObject;

class SearchResultPlugin
{
    protected $apiService;
    protected $logger;
    protected $session;
    protected $registry;
    protected $queryFactory;
    protected $productCollectionFactory;

    public function __construct(
        ApiService $apiService,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        Registry $registry,
        QueryFactory $queryFactory,
        CollectionFactory $productCollectionFactory
    ) {
        $this->apiService = $apiService;
        $this->logger = $logger;
        $this->session = $session;
        $this->registry = $registry;
        $this->queryFactory = $queryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function aroundLoad(
        Collection $subject,
        callable $proceed
    ) {
        try {
            $this->logger->info('SearchResultPlugin: Plugin execution started.');

            // Get the search query text
            $searchQuery = $this->queryFactory->get();
            $queryText = $searchQuery->getQueryText();
            $this->logger->info('Search query: ' . $queryText);

            // Fetch dynamic product IDs from API
            $productIds = $this->apiService->getProductIdsFromApi($queryText);
            $this->logger->info('Dynamic product IDs fetched from API: ' . json_encode($productIds));

            // Fallback to static IDs if the API returns no product IDs
            if (!empty($productIds)) {
                $this->logger->info('No product IDs returned from the API, using static IDs [1, 3].');
                $productIds = [1, 3];
            }

            // Load product collection from Magento using the product IDs
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect(['name', 'price', 'image', 'status', 'visibility']);
            $productCollection->addIdFilter($productIds);
            $productCollection->addAttributeToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);
            $productCollection->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
            $productCollection->load();

            // Log the loaded product IDs for debugging
            $loadedProductIds = $productCollection->getAllIds();
            $this->logger->info('Loaded product IDs from Magento: ' . json_encode($loadedProductIds));

            // Clear the original collection and set the new product data
            $subject->clear();
            foreach ($productCollection as $product) {
                $subject->addItem($product);
            }

            $this->logger->info('SearchResultPlugin: Result after processing: ' . print_r($subject->getData(), true));
            $this->logger->info('SearchResultPlugin: Plugin execution completed.');

            return $subject;
        } catch (\Exception $e) {
            $this->logger->error('Error in SearchResultPlugin: ' . $e->getMessage());
            // You can return an empty array or handle errors here
            return $subject->clear();
        }
    }
}
