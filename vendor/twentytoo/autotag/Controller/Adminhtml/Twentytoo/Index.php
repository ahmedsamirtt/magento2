<?php
namespace TwentyToo\AutoTag\Controller\Adminhtml\Twentytoo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Index extends Action implements HttpPostActionInterface
{
    protected $resultPageFactory;
    protected $formKey;
    protected $logger;
    protected $_formKeyValidator;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        LoggerInterface $logger,
        \Magento\Framework\App\Request\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKey = $formKey;
        $this->logger = $logger;
        $this->_formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        try {
            $this->logger->info('Form key generated: ' . $this->formKey->getFormKey());

            // Validate form key
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new \Exception('Invalid form key');
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Welcome to TwentyToo'));
        
            // Log form key generation
            $formKey = $this->formKey->getFormKey();
            $this->logger->info('Form key generated: ' . $formKey);
        
            // Add form key to the result page
            $resultPage->getLayout()->getBlock('twentytoo_welcome')
                ->setData('form_key', $formKey);
                
            return $resultPage;
        } catch (\Exception $e) {
            // Log error message
            $this->logger->error('Error in Index controller: ' . $e->getMessage());
            
            // Redirect to a specific error page or display a generic error message
            $this->_redirect('*/*/error');
        }
    }
}
