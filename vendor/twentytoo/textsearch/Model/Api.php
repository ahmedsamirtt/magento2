<?php

namespace TwentyToo\TextSearch\Model;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class Api implements ApiInterface
{
    protected $httpClient;
    protected $logger;

    public function __construct(Curl $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function fetchData($query)
    {
        $apiUrl = 'https://apidev.twentytoo.ai/cms/v2/text-search';
        $payload = [
            'text' => $query,
            'page' => 1,
            'page_limit' => 12,
            'filters' => [
                ['website' => []],
                ['target_audience' => []],
                ['department' => []]
            ],
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'x-tt-api-key' => '32Z4tGu2GI9zmSPH8aJg06KmAN1ljV0UaOBDOLnp'
        ];

        try {
            $this->httpClient->addHeader('Content-Type', 'application/json');
            $this->httpClient->addHeader('x-tt-api-key', '32Z4tGu2GI9zmSPH8aJg06KmAN1ljV0UaOBDOLnp');
            $this->httpClient->post($apiUrl, json_encode($payload));

            $response = $this->httpClient->getBody();
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
