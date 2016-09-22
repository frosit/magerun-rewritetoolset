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

namespace Frosit\Utils;

use Frosit\Utils\Curl\MultiCurl;
use Frosit\Utils\Curl\Curl;
use Frosit\Utils\Magento\Urls as MagentoUrls;

/**
 * Class AbstractGetSet
 * @package Frosit\Magento
 */
class Utils
{

    protected $_curl;
    protected $_multicurl;
    protected $_mageurl;


    /**
     * constructor to run at every command
     */
    public function __construct()
    {

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

}