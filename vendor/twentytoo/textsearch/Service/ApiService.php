<?php

namespace TwentyToo\TextSearch\Service;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;

class ApiService
{
    protected $logger;
    protected $curl;

    public function __construct(
        LoggerInterface $logger,
        Curl $curl
    ) {
        $this->logger = $logger;
        $this->curl = $curl;
    }

    public function getProductIdsFromApi($query)
    {
        $apiUrl = 'https://apidev.twentytoo.ai/cms/v2/text-search';
        $payload = json_encode([
            'text' => $query,
            'page' => 1,
            'page_limit' => 12,
            'filters' => [
                ['website' => []],
                ['target_audience' => []],
                ['department' => []]
            ],
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'x-tt-api-key' => '32Z4tGu2GI9zmSPH8aJg06KmAN1ljV0UaOBDOLnp'
        ];

        try {
            $this->curl->setHeaders($headers);
            $this->curl->post($apiUrl, $payload);
            $response = $this->curl->getBody();
            $responseData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('API Error: ' . json_last_error_msg());
                return [];
            }

            $productIds = [];
            if (!empty($responseData['message'])) {
                foreach ($responseData['message'] as $product) {
                    if (!empty($product['product_id'])) {
                        $productIds[] = $product['product_id'];
                    }
                }
            }

            return $productIds;

        } catch (\Exception $e) {
            $this->logger->error('API Error: ' . $e->getMessage());
            return [];
        }
    }
}
