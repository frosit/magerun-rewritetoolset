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

/**
 * Dev notes
 *
 * @todo add whitelist
 * @refactor whole section
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

}