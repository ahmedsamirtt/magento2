<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Index extends Action
{
    public function execute()
    {
        $resultJson = $this->_objectManager->create(\Magento\Framework\Controller\Result\JsonFactory::class)->create();
        $resultJson->setData(['message' => 'Hello Twenty Too']);
        return $resultJson;
    }
}