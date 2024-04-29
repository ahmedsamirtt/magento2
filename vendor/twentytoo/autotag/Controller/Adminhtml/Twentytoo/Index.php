<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Twentytoo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $resultPageFactory;
    protected $formKey;
    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKey = $formKey;
        $this->logger = $logger;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Welcome to TwentyToo'));

        // Log form key generation
        $formKey = $this->formKey->getFormKey();
        $this->logger->info('Form key generated: ' . $formKey);

        // Add form key to the result page
        $resultPage->getLayout()->getBlock('twentytoo_welcome')
            ->setData('form_key', $formKey);
        
        return $resultPage;
    }
}
