<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Twentytoo;

class Index extends \Magento\Backend\App\Action
{
    protected $_publicActions = ['index'];

    public function execute()
    {
        // Your controller logic goes here
        echo "Hello from TwentyToo!";
        exit;
    }
}