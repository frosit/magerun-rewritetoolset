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
 * @todo test config overrides
 * @todo add new resource helper
 */

namespace Frosit\Magento\Command\Rewrites;

use Frosit\Magento\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractRewritesCommand
 * @package Frosit\Magento\Command\Rewrites
 */
abstract class AbstractRewritesCommand extends AbstractCommand
{

    public function __construct()
    {
        parent::__construct();
        $this->setOptions();
    }

    /**
     * Set default options across commands
     * @todo add option override from yaml config
     */
    private function setOptions()
    {
        $this->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Stores to process, by default it asks. options:[all,int,string,array]');
    }

    /**
     * @return string
     */
    protected function getDataDir()
    {
        return $this->getRewritesDataDir('data');
    }

    /**
     * Prepares User store input
     * @todo add result validation
     * @param null $input
     * @param bool $onlyStoreIds
     * @param bool $validateResult
     * @return array
     */
    public function prepareStoresFromInput($input = null, $onlyStoreIds = false, $validateResult = true)
    {

        $stores = array();
        $oid = $onlyStoreIds;
        if (strpos($input, ",")) {
            $input = explode(",", $input);
        }
        $inputType = gettype($input);
        switch ($inputType) {
            case "string":
                if ($input == "*" || $input == "all") {
                    $stores = $this->getAllStores($oid);
                } else {
                    $stores[] = is_numeric(intval($inputType)) ? $this->getStoreById($input, $oid) : $this->getStoreByCode($input, $oid);
                }
                break;
            case "integer":
                $stores[] = $this->getStoreById($input, $oid);
                break;
            case "array":
                foreach ($input as $item) {
                    $st = self::prepareStoresFromInput($item, $oid, false);
                    $stores[] = $st[0]; // todo test this
                }
                break;
            case "NULL":
            case "null":
                $stores[] = $this->askStore($oid);
                break;
            default:
                $this->_error('Could not identify input type, asking you instead');
                $stores[] = $this->askStore($oid);
                break;
        }
        return $stores;
    }

    /**
     * @param $code
     * @param bool $onlyIds
     * @return mixed
     */
    public function getStoreByCode($code, $onlyIds = false)
    {
        $store = \Mage::getModel('core/store')->load($code, 'code');
        return $onlyIds ? $store->getId() : $store->getData();
    }

    /**
     * @param $id
     * @param bool $onlyIds
     * @return mixed
     */
    public function getStoreById($id, $onlyIds = false)
    {
        $store = \Mage::getModel('core/store')->load($id);
        return $onlyIds ? $store->getId() : $store->getData();
    }

    /**
     * @param bool $onlyIds
     * @return array
     */
    public function getAllStores($onlyIds = false)
    {
        $storeModel = \Mage::getModel('core/store')->getCollection();
        $stores = $storeModel->getData();
        return $onlyIds ? array_column($stores, 'store_id') : $stores;
    }

    /**
     * @param bool $onlyIds
     * @return mixed
     */
    public function askStore($onlyIds = false)
    {
        $store = $this->getHelper('parameter')->askStore($this->_input, $this->_output)->getData();
        return $onlyIds ? $store['store_id'] : $store;
    }

    /**
     * Gets the connection
     * @return mixed
     */
    public function getConnection()
    {
        $resource = \Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        return $readConnection;
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
     * Cleans out bad urls
     * @todo duplicate function
     * @param $url
     * @return bool
     */
    public function validateUrl($url)
    {
        $ignoreValues = array("?", "&", "=", ":", "~", "%", ")", ","); // add all characters not supposed in string for url rewrite
        $customIgnores = array("/w/", "__", "_-", "-_", "/form_key/", "uenc", ".php", ".git", "/../", "/sendfriend/product/send/"); // add default magento paths, modules (layered nav), commong hacks
        $acceptedExtensions = array("html"); // also get extension from store

        $valid = true;
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        foreach ($ignoreValues as $ignoreValue) {
            if (strpos($url, $ignoreValue)) {
                $valid = false;
                break;
            }
        }
        if ($valid && $ext) {
            if (!in_array($ext, $acceptedExtensions)) {
                $valid = false;
                // maybe continue
            }
        }
        if ($valid) {
            foreach ($customIgnores as $customIgnore) {
                if (strpos($url, $customIgnore)) {
                    $valid = false;
                    break;
                }
            }
        }
        return $valid ? $url : false;
    }
}
