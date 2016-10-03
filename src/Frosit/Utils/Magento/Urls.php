<?php
/**
 * Frosit URLRewriteToolset
 *
 * @category    magerun-addon
 * @package     URLRewriteToolset
 * @author      Fabio Ros <f.ros@fros.it>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
/**
 * Dev notes
 *
 * @todo refactor or remove
 *
 */

namespace Frosit\Utils\Magento;

/**
 * Class Urls
 * @package Frosit\Utils\Magento
 */
class Urls
{

    /**
     * Prepares the config array
     * @param $config
     * @return array
     */
    public function prepareConfig($config)
    {
        $sc = [
            "products" => true,
            "categories" => false,
            "cms" => false,
            "limit" => false,
            "random" => false,
            "returnAssoc" => false,
        ];
        foreach ($config as $key => $value) {
            $sc[$key] = $value;
        }
        return $sc;
    }

    /**
     * Fetches store URL's
     * @param array|bool $stores
     * @param array $config
     * @param bool $save
     * @return array
     */
    public function getUrls($stores = [], $config = [], $save = false)
    {
        $config = $this->prepareConfig($config);
        $urls = array();

        foreach ($stores as $store_view) {

            $storeId = $store_view['store_id'];
            $baseUrl = \Mage::app()->getStore($storeId)->getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_LINK);
            $storeUrls = array();

            if (!$config['products']) {
                $storeUrls['product'] = $this->appendSettings($this->getProductUrls($storeId, $baseUrl), $config);
            }

            if (!$config['categories']) {
                $storeUrls['category'] = $this->appendSettings($this->getCategoryUrls($storeId, $baseUrl), $config);
            }

            if (!$config['cms']) {
                $storeUrls['cms'] = $this->appendSettings($this->getCmsUrls($storeId, $baseUrl), $config);
            }

            if (!$config['returnAssoc']) {
                $assocUrls = array();
                foreach ($storeUrls as $storeUrl) {
                    foreach ($storeUrl as $item) {
                        $assocUrls[] = $item;
                    }
                }
                $storeUrls = $assocUrls;
            }
            $urls = array_merge($urls, $storeUrls);
        }

        if ($save) {
            $this->urlsToSitemapXml($urls, true);
        }

        return $urls;
    }

    /**
     * Gets product Urls
     * @param $storeId
     * @param $baseUrl
     * @return array
     */
    public function getProductUrls($storeId, $baseUrl)
    {
        $urls = array();

        $collection = \Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        $products = new \Varien_Object();
        $products->setItems($collection);
        foreach ($products->getItems() as $item) {
            $urls[] = $baseUrl . $item->getUrl();
        }
        return $urls;
    }

    /**
     * Gets Category Urls
     * @param $storeId
     * @param $baseUrl
     * @return array
     */
    public function getCategoryUrls($storeId, $baseUrl)
    {
        $urls = array();
        $collection = \Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        $categories = new \Varien_Object();
        $categories->setItems($collection);
        foreach ($categories->getItems() as $item) {
            $urls[] = $baseUrl . $item->getUrl();
        }
        return $urls;
    }

    /**
     * Gets CMS URL's
     * @param $storeId
     * @param $baseUrl
     * @return array
     */
    public function getCmsUrls($storeId, $baseUrl)
    {
        $urls = array();
        $collection = \Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $urls[] = $baseUrl . $item->getUrl();
        }
        return $urls;
    }

    /**
     * Does some modifications to URLs
     * @param $urls
     * @param $config
     * @return array|mixed
     */
    public function appendSettings($urls, $config)
    {
        $preparedUrls = array();
        if ($config['limit']) {
            if ($config['random']) {
                $randomKeys = array_rand($urls, $config['limit']);
                foreach ($randomKeys as $key) {
                    $preparedUrls[] = $urls[$key];
                }

            } else {
                $preparedUrls = array_slice($urls, 0, $config['limit']);
            }
        } else {
            $preparedUrls = $urls;
        }
        return $preparedUrls;
    }

    /**
     * Check the base url and return the real one or current
     * @todo write this func
     * @param $baseUrl
     * @return mixed
     */
    public function checkBaseUrl($baseUrl)
    {
        return $baseUrl;
    }

    /**
     * Convert an URL list to sitemap structure
     * @param $urls
     * @param bool $save
     * @return string
     */
    public function urlsToSitemapXml($urls, $save = false)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>' . $url . '</loc></url>';
        }
        $xml .= '</urlset>';

        if ($save) {

            $filename = 'generated_sitemap_' . time() . '.xml';
            if (!is_bool($save)) {
                $filename = $save . '.xml';
            }

            file_put_contents($filename, $xml);
        }
        return $xml;
    }
}