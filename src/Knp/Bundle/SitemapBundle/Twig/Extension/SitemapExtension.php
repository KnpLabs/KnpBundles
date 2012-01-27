<?php
namespace Knp\Bundle\SitemapBundle\Twig\Extension;

use InvalidArgumentException;

class SitemapExtension extends \Twig_Extension
{
    private $baseUrl;

    public function __construct($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getFilters()
    {
        return array(
            'kb_sitemap_url_absolute' => new \Twig_Filter_Method($this, 'absoluteUrl'),
            'kb_sitemap_date' => new \Twig_Filter_Method($this, 'formatDate'),
        );
    }

    public function absoluteUrl($path)
    {
        return $this->baseUrl.'/'.ltrim($path, '/');
    }

    public function formatDate(\DateTime $date)
    {
        // YYYY-MM-DDThh:mmTZD
        return $date->format('Y-m-d');
    }

    public function getName()
    {
        return 'sitemap';
    }
}