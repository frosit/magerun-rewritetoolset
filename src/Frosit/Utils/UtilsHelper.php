<?php
/**
 * Frosit magerun RewriteToolset
 *
 * @category    project
 * @package     magerun-RewriteToolset
 * @author      Fabio Ros <info@frosit.nl>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 *
 */

namespace Frosit\Utils;

use Frosit\Utils\Csv\parseCSV;
use Frosit\Utils\Curl\MultiCurl;
use Frosit\Utils\Curl\Curl;
use Frosit\Utils\Magento\Urls as MagentoUrls;

use Frosit\Utils\MicroDB\Cache;
use Frosit\Utils\MicroDB\Database;
use Frosit\Utils\MicroDB\Event;
use Frosit\Utils\MicroDB\Index;

/**
 * Class AbstractGetSet
 * @package Frosit\Magento
 */
class UtilsHelper
{

    protected $_curl;
    protected $_multicurl;
    protected $_mageurl;
    protected $_parseCsv;

    public $mdbcache;
    public $mdb;
    public $mdbevent;
    public $mdbindex;


    /**
     * constructor to run at every command
     */
    public function __construct()
    {

    }

    /**
     * @return parseCSV
     */
    public function getParseCsv()
    {
        if (null === $this->_parseCsv) {
            $this->_parseCsv = new parseCSV();
        }
        return $this->_parseCsv;
    }

    /**
     * @param parseCSV $parseCSV
     * @return $this
     */
    public function setParseCsv(parseCSV $parseCSV)
    {
        $this->_parseCsv = $parseCSV;
        return $this;
    }

    /**
     * Get the cURL class
     * @return Curl
     */
    public function getCurl()
    {
        if (null === $this->_curl) {
            $this->_curl = new Curl();
        }
        return $this->_curl;
    }

    /**
     * Set the cURL Class
     * @param Curl $curl
     * @return $this
     */
    public function setCurl(Curl $curl)
    {
        $this->_curl = $curl;
        return $this;
    }

    /**
     * Gets the Multicurl class
     * @return MultiCurl
     */
    public function getMultiCurl()
    {
        if (null === $this->_multicurl) {
            $this->_multicurl = new MultiCurl();
        }
        return $this->_multicurl;
    }

    /**
     * Sets the multicurl class
     * @param MultiCurl $multicurl
     * @return $this
     */
    public function setMultiCurl(MultiCurl $multicurl)
    {
        $this->_multicurl = $multicurl;
        return $this;
    }

    /**
     * Gets the Magento URLs class
     * @return MagentoUrls
     */
    public function getMagentoUrls()
    {
        if (null === $this->_mageurl) {
            $this->_mageurl = new MagentoUrls();
        }
        return $this->_mageurl;
    }

    /**
     * Sets the MagentoUrls class
     * @param MagentoUrls $mageurl
     * @return $this
     */
    public function setMagentoUrls(MagentoUrls $mageurl)
    {
        $this->_mageurl = $mageurl;
        return $this;
    }


    /**
     * @param $mdb
     * @return Cache
     */
    public function getMdbcache($mdb)
    {
        if (null === $this->mdbcache) {
            $this->mdbcache = new Cache($mdb);
        }
        return $this->mdbcache;
    }

    /**
     * @param Cache $mdbcache
     * @return $this
     */
    public function setMdbcache(Cache $mdbcache)
    {
        $this->mdbcache = $mdbcache;
        return $this;
    }

    /**
     * @param $mdb
     * @return Database
     */
    public function getMdb($mdb)
    {
        if (null === $this->mdb) {
            $this->mdb = new Database($mdb);
        }
        return $this->mdb;
    }

    /**
     * @param Database $mdb
     * @return $this
     */
    public function setMdb(Database $mdb)
    {
        $this->mdb = $mdb;
        return $this;
    }

    /**
     * @param $mdb
     * @param $id
     * @param $data
     * @return Event
     */
    public function getMdbevent($mdb, $id, $data)
    {
        if (null === $this->mdbevent) {
            $this->mdbevent = new Event($mdb, $id, $data);
        }
        return $this->mdbevent;
    }

    /**
     * @param Event $mdbevent
     * @return $this
     */
    public function setMdbevent(Event $mdbevent)
    {
        $this->mdbevent = $mdbevent;
        return $this;
    }

    /**
     * @param $mdb
     * @param $name
     * @param $keyFunc
     * @param null $compare
     * @return Index
     */
    public function getMdbindex($mdb, $name, $keyFunc, $compare = null)
    {
        if (null === $this->mdbindex) {
            $this->mdbindex = new Index($mdb, $name, $keyFunc, $compare);
        }
        return $this->mdbindex;
    }

    /**
     * @param Index $mdbindex
     * @return $this
     */
    public function setMdbindex(Index $mdbindex)
    {
        $this->mdbindex = $mdbindex;
        return $this;
    }

}