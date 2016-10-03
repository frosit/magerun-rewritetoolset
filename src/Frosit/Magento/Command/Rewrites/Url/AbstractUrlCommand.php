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

namespace Frosit\Magento\Command\Rewrites\Url;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;

/**
 * Class AbstractUrlCommand
 * @package Frosit\Magento\Command\Rewrites\Url
 */
abstract class AbstractUrlCommand extends AbstractRewritesCommand
{
    public $dbpath;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @dep?
     * @return mixed
     */
    public function initDbCommands()
    {
        return $this->getApplication()->getConfig('rwtoolset')['rwtoolset'];
    }

    /**
     * Merges urls gathered from different sources
     * @return array
     */
    public function getMergedGatheredUrlsFromDb($cleanExtra = true)
    {
        $sources = array("csv", "log", "visitor");
        $dataDir = $this->getDataDir();
        $mergeAbleSources = [];
        foreach ($sources as $source) {
            $sourceDir = $dataDir . '/' . $source;
            $souceItems = scandir($sourceDir);
            foreach ($souceItems as $souceItem) {
                if (intval($souceItem) && file_exists($item = $sourceDir . '/' . $souceItem)) {
                    $mergeAbleSources[] = $item;
                }
            }
        }

        $urlCollection = array();
        foreach ($mergeAbleSources as $ableSource) {
            $content = file_get_contents($ableSource);
            $urls = json_decode($content);

            if ($cleanExtra) {
                $validatedUrls = [];
                foreach ($urls as $url) {
                    if ($url = $this->validateUrl($url)) {
                        $validatedUrls[] = $url;
                    }
                }
                $urlCollection = array_merge($validatedUrls, $urlCollection);
            } else {
                $urlCollection = array_merge($urls, $urlCollection);
            }
            $urlCollection = array_unique($urlCollection);
        }
        asort($urlCollection);
        return $urlCollection;
    }


}