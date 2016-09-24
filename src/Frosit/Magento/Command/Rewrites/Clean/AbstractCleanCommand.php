<?php
/**
 * Frosit magerun RewriteToolset
 *
 * @category    project
 * @package     magerun-RewriteToolset
 * @author      Fabio Ros <info@frosit.nl>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace Frosit\Magento\Command\Rewrites\Clean;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;
use Frosit\Utils\Csv\parseCSV;
use Frosit\Utils\Mysql\MysqliDb;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractCleanCommand
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
abstract class AbstractCleanCommand extends AbstractRewritesCommand
{

    public $db;

    protected $_disabledValue = 2; // probably ::STATUS_DISABLED
    protected $_enabledValue = 1; // probably ::STATUS_ENABLED

    public function __construct()
    {
        parent::__construct();

        $this->setOptions();
    }

    /**
     * Set default options across commands
     */
    private function setOptions()
    {
        $this->addOption('dry-run');
    }

    /**
     * @return MysqliDb
     */
    public function getDb()
    {
        if (null === $this->db) {
            $this->db = new MysqliDb($this->getDbCredentials());
        }
        return $this->db;
    }

    /**
     * @param MysqliDb $mysqliDb
     * @return $this
     */
    public function setDb(MysqliDb $mysqliDb)
    {
        $this->db = $mysqliDb;
        return $this;
    }

    /**
     * Check if a product is disabled in store
     * @param $storeId
     * @param $productId
     * @return bool
     */
    public function isProductDisabledForStore($storeId, $productId)
    {
        $product = \Mage::getModel('catalog/product')->setStore()->setStoreId($storeId)->load($productId);
        $status = $product->getStatus();
        return $status == $this->_disabledValue ? true : false;
    }

    /**
     * Fetches URLs from CSV
     * @param $filename
     * @param $column
     * @return array|bool
     */
    public function getUrlsFromCsv($filename, $column)
    {
        $file = $this->_magentoRootFolder . DS . $filename;
        if (file_exists($file)) {
            $csv = new parseCSV();
            $csv->auto($file);
            $urlList = array_column($csv->data, $column);
            $cleanUrls = $this->cleanUrls($urlList);
            return $cleanUrls;
        } else {
            return false;
        }
    }

    /**
     * Cleans out bad URLs
     * @param $urls
     * @return array
     */
    public function cleanUrls($urls)
    {
        $cleanUrls = array();
        foreach ($urls as $url) {
            $badUrl = false;
            if (strpos($url, "?")) {
                continue;
            }
            if ($url == "") {
                continue;
            }
            $url = ltrim($url, "/");
            if (!$badUrl) {
                $cleanUrls[] = $url;
            }
        }
        return $cleanUrls;
    }


    /**
     * Fetches DB Credentials
     * @return array
     */
    public function getDbCredentials()
    {
        $config = \Mage::getConfig()->getResourceConnectionConfig("default_setup");
        $credentials = array(
            "host" => (string)$config->host,
            "username" => (string)$config->username,
            "password" => (string)$config->password,
            "db" => (string)$config->dbname
        );
        return $credentials;
    }

    /**
     * Gets urls for the visitor log table
     * @return array
     */
    public function getLoggedUrls()
    {
        $db = $this->getDb();
        $urlInfo = $db->get('log_url_info');
        $urls = array_column($urlInfo, 'url');
        return $urls;
    }

    /**
     * Gets the id's that are whitelisted
     * @param $urls
     * @param bool $store
     */
    public function getWhitelistedRewriteIds($urls, $store = false)
    {
        $ids = array();
        $db = $this->getDb();
        foreach ($urls as $url) {
            $db->where('request_path', $url);
            if ($store) {
                $db->where('store_id', $store['store_id']);
            }
            $rewrites = $db->get('core_url_rewrite', 500, 'url_rewrite_id');
            if ($db->count > 0) {
                // @todo finish
            }
        }
    }

}