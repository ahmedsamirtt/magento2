<?php
namespace TwentyToo\TextSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Session\SessionManagerInterface;

class SearchResults extends Template
{
    protected $session;

    public function __construct(
        Template\Context $context,
        SessionManagerInterface $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
    }

    public function getProducts()
    {
        return $this->session->getSearchResults();
    }
}
