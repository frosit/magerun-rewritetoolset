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

namespace Frosit\Magento\Command\Rewrites\Fix;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;
use Frosit\Utils\Mysql\MysqliDb;

/**
 * Class AbstractFixCommand
 * @package Frosit\Magento\Command\Rewrites\Fix
 */
abstract class AbstractFixCommand extends AbstractRewritesCommand
{

    public $db;

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
     * Gets entity type
     * @param $code
     * @return mixed
     */
    public function getEntityType($code)
    {
        $type = \Mage::getModel('eav/entity_type')->loadByCode($code);
        return $type;
    }

    /**
     * Get store config
     * @param $setting
     * @param null $storeId
     * @return mixed
     */
    public function getSeoConfiguration($setting, $storeId = null)
    {
        if (is_array($storeId)) {
            $storeId = $storeId['store_id'];
        }
        return \Mage::getStoreConfig($setting, $storeId);
    }

    /**
     * Cleans out bad URL characters
     * @param $url
     * @return mixed|string
     */
    public function cleanUrlChars($url)
    {
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
        return $url;
    }
}