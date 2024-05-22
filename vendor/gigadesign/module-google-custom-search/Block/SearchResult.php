<?php
/**
 * Copyright © Gigadesign. All rights reserved.
 */
declare(strict_types=1);

namespace Gigadesign\GoogleCustomSearch\Block;

use Gigadesign\GoogleClient\Model\GoogleClientManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SearchResult extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var GoogleClientManager
     */
    private $googleClientManager;

    /**
     * @var string
     */
    private $query;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param GoogleClientManager $googleClientManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        GoogleClientManager $googleClientManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->googleClientManager = $googleClientManager;

        parent::__construct($context, $data);
    }

    /**
     * @return \Google_Service_Customsearch_Search
     *
     * @throws NoSuchEntityException
     */
    public function getSearchResultCollection()
    {
        $this->googleClientManager->setDeveloperKey(
            $this->scopeConfig->getValue('catalog/search/googlecustomsearch_api_key', ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getCode())
        );

        /** @var \Google_Service_Customsearch $customSearch */
        $customSearch = $this->googleClientManager->getService('Customsearch');

        $result = $customSearch->cse->listCse($this->getSearchQuery(), [
            'cx' => $this->scopeConfig->getValue('catalog/search/googlecustomsearch_engine_id', ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getCode())
        ]);

        return $result;
    }

    /**
     * Get search query text
     *
     * @return Phrase
     */
    public function getSearchQueryText()
    {
        return __("Search results for: '%1'", $this->getSearchQuery());
    }

    /**
     * Prepare layout
     *
     * @return Template
     *
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $title = $this->getSearchQueryText();
        $this->pageConfig->getTitle()->set($title);
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => $title, 'title' => $title]
            );
        }

        return parent::_prepareLayout();
    }

    private function getSearchQuery()
    {
        if (!$this->query) {
            $this->query = $this->request->getParam('q');
        }

        return $this->query;
    }
}
