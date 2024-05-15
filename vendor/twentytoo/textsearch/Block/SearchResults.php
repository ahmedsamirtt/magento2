<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class SearchResults extends Template
{
    protected $session;
    protected $productRepository;
    protected $logger;

    public function __construct(
        Template\Context $context,
        SessionManagerInterface $session,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getProductsFromSession()
    {
        $productIds = $this->session->getSearchResults();
        $products = [];
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                try {
                    $product = $this->productRepository->getById($productId);
                    $products[] = $product;
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->logger->error('Product not found: ' . $productId);
                }
            }
        }
        return $products;
    }
}
