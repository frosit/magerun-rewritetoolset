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

namespace Frosit\Magento\Command\Rewrites\Benchmark;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**+
 * Class SitemapCommand
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class ResolveUrlCommand extends AbstractBenchmarkCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:benchmark:resolve-url')
            ->setDescription('Benchmarks URL resolve times by triggering sitemap generate actions.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        } else {
            $this->_input = $input;
            $this->_output = $output;
            $this->setRewriteToolsCommandConfig($this->getCommandConfig());
        }

        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        // header
        $this->writeSection($output, 'Rewrite Benchmark - Resolving URLs [FROSIT]');

        $statistics = $this->gatherStatistics($stores);

        // fi
        $this->processCommandEnd($statistics);
    }


    /**
     * Gathers the statistics by executing the querys
     * @param $stores
     * @param array $entities
     * @return array
     */
    public function gatherStatistics($stores, $entities = array("products", "categories"))
    {
        $statistics = array();
        foreach ($stores as $store) {
            if ($store['code'] != 'admin') {

                $storeId = $store['store_id'];
                $mstartTime = new \DateTime('now');

                $storeData = array(
                    "id" => $store['store_id'],
                    "code" => $store['code'],
                    "name" => $store['name'],
                    "active" => $store['is_active'],
                    "started" => $mstartTime->getTimestamp()
                );
                foreach ($entities as $entity) {
                    $urls = array();
                    $startTime = microtime(true);
                    if ($entity == "products") {
                        $urls = $this->getProductUrls($storeId);
                    } elseif ($entity == "categories") {
                        $urls = $this->getCategoryUrls($storeId);
                    }
                    $endTime = microtime(true);
                    $runtime = $endTime - $startTime;
                    $totalUrls = count($urls);

                    $storeData[$entity] = array(
                        "total_urls" => $totalUrls,
                        "start_time" => $startTime,
                        "end_time" => $endTime,
                        "runtime" => $runtime,
                        "urls" => $urls
                    );

                    $this->_info("finished resolving: <comment>" . $entity . "</comment> in <comment>" . $runtime . "</comment> for store: <comment>" . $store['name'] . "</comment>. Resolved urls: <comment>" . $totalUrls . "</comment>");
                }
                $mendTime = new \DateTime('now');
                $storeData['ended'] = $mendTime->getTimestamp();
                $storeData['runtime'] = $mendTime->getTimestamp() - $mstartTime->getTimestamp();
                array_push($statistics, $storeData);
            }
        }
        return $statistics;
    }

    /**
     * Gets product urls
     * @param $storeId
     * @return array
     * @internal param $baseUrl
     */
    public function getProductUrls($storeId)
    {
        $urls = array();
        $collection = \Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        $products = new \Varien_Object();
        $products->setItems($collection);

        foreach ($products->getItems() as $item) {
            $urls[] = $item->getData();
        }
        return $urls;
    }

    /**
     * Gets Category Urls
     * @param $storeId
     * @return array
     * @internal param $baseUrl
     */
    public function getCategoryUrls($storeId)
    {
        $urls = array();
        $collection = \Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        $categories = new \Varien_Object();
        $categories->setItems($collection);
        foreach ($categories->getItems() as $item) {
            $urls[] = $item->getData();
        }
        return $urls;
    }

}