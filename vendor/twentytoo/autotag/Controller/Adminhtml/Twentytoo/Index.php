<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Twentytoo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;
    protected $formKey;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKey = $formKey;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Welcome to TwentyToo'));

        // Add form key to the result page
        $resultPage->getLayout()->getBlock('twentytoo_welcome')
            ->setData('form_key', $this->formKey->getFormKey());

        return $resultPage;
    }
}
