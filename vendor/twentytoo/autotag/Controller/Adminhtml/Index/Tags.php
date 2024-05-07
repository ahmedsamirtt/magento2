<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Tags extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            // Make HTTP request
            $response = $this->makeHttpRequest('http://userbehavior-ml-nlb-e9c564bf7db7f4eb.elb.us-east-2.amazonaws.com/');
            
            // Log the response
            $this->logger->info('HTTP request response: ' . $response);

            // Return success message
            $response = ['success' => true, 'message' => 'HTTP request successful.'];
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error($e->getMessage());

            // Return error message
            $response = ['success' => false, 'message' => 'An error occurred.'];
        }

        return $resultJson->setData($response);
    }

    /**
     * Make HTTP request and return the response.
     *
     * @param string $url The URL to make the request to.
     * @return string The response from the HTTP request.
     * @throws \Exception If an error occurs during the request.
     */
    protected function makeHttpRequest($url)
    {
        // Make HTTP request using file_get_contents() or cURL
        $response = file_get_contents($url);
        // If using cURL, you can use the following:
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $response = curl_exec($ch);
        // curl_close($ch);

        if ($response === false) {
            throw new \Exception('Error making HTTP request.');
        }

        return $response;
    }
}
