<?php
/**
 * Copyright © Gigadesign. All rights reserved.
 */
declare(strict_types=1);

namespace Gigadesign\GoogleCustomSearch\Plugin;

use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Search\Helper\Data as Subject;
use Magento\Search\Model\QueryFactory;

/**
 * Plugin to update search url's when Google Custom Search is enabled
 *
 * @author Mark van der Werf <mark@gigadesign.nl>
 */
class UpdateUrlsWhenCustomSearchEnabledPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param Subject $subject
     * @param string $result
     * @param string|null $query
     *
     * @return string
     */
    public function afterGetResultUrl(Subject $subject, string $result, $query = null): string
    {
        if ($this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH)) {
            return $this->_getUrl(
                'search/result',
                ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->request->isSecure()]
            );
        }

        return $result;
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
