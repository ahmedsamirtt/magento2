<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['message' => 'Hello Twenty Too']);
        return $resultJson;
    }
}
