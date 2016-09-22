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

namespace Frosit\Magento\Command\Rewrites\Analysis;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TotalsCommand
 * @package Frosit\Magento\Command\Rewrites\Analysis
 */
class TotalsCommand extends AbstractAnalysisCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:analysis:totals')
            ->setDescription('Calculates the amount and percentage of duplicate rewrites per store.');
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

        // header
        $this->writeSection($output, 'Rewrite statistics - Totals [FROSIT]');

        // stores
        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        // stats
        $statistics = $this->gatherStatistics($stores);

        // table
        $tableHelper = $this->getHelper('table');
        $tableHelper->setHeaders(array("Store", "Prod. Dupes", "% Prod. Dupes", "Cat. Dupes", "% Cat. Dupes"));
        $tableHelper->renderByFormat($output, $this->prepareTableData($statistics));

        // finish
        $this->processCommandEnd($statistics);
    }

    /**
     * Prepares the data for outputting a table
     * @param $statistics
     * @return array
     */
    public function prepareTableData($statistics)
    {
        $tablestats = array();
        foreach ($statistics as $statistic) {

            $storeStat = array();
            $storeStat[] = $statistic['name'];
            $storeStat[] = $statistic['duplicate_product_rewrites'];
            $storeStat[] = $statistic['percentage_duplicate_product_rewrites'];
            $storeStat[] = $statistic['duplicate_category_rewrites'];
            $storeStat[] = $statistic['percentage_duplicate_category_rewrites'];
            $tablestats[] = $storeStat;
        }
        return $tablestats;
    }

    /**
     * Gathers the statistics by executing the querys
     * @param $stores
     * @return array
     */
    public function gatherStatistics($stores)
    {
        $statistics = array();
        foreach ($stores as $store) {
            if ($store['code'] != 'admin') {
                $storeData = array(
                    "id" => $store['store_id'],
                    "code" => $store['code'],
                    "name" => $store['name'],
                    "active" => $store['is_active'],
                );
                $rewriteCounts = $this->getRewriteCounts($store['store_id'], true);
                $results = array_merge($storeData, $rewriteCounts);
                array_push($statistics, $results);
            }
        }
        return $statistics;
    }


    /**
     * Get Counted values of product and category rewrites by store id
     * @todo optimize this
     * @param $storeId
     * @param bool $percentages
     * @return array
     */
    public function getRewriteCounts($storeId, $percentages = false)
    {
        $connection = $this->getConnection();
        $results = array(
            "unique_product_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `is_system` = 1 AND `product_id` REGEXP '^[0-9]+$'")),
            "duplicate_product_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `is_system` = 0 AND `options` = 'RP' AND `product_id` REGEXP '^[0-9]+$'")),
            "total_product_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `product_id` REGEXP '^[0-9]+$'")),
            "unique_category_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `is_system` = 1 AND `category_id` REGEXP '^[0-9]+$' AND `product_id` IS NULL")),
            "duplicate_category_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `is_system` = 0 AND `options` = 'RP' AND `category_id` REGEXP '^[0-9]+$' AND `product_id` IS NULL")),
            "total_category_rewrites" => $connection->fetchOne($this->getRewritesQuery('COUNT(*)', " `store_id` = " . $storeId . " AND `category_id` REGEXP '^[0-9]+$' AND `product_id` IS NULL"))
        );

        if ($percentages) {
            $results["percentage_duplicate_product_rewrites"] = round((($results['duplicate_product_rewrites'] / $results['total_product_rewrites']) * 100), 2) . '%';
            $results["percentage_duplicate_category_rewrites"] = round((($results['duplicate_category_rewrites'] / $results['total_category_rewrites']) * 100), 2) . '%';
        }
        return $results;
    }

    /**
     * Legacy code for building a query
     * @todo remove this after optimizing
     * @see $this->getRewriteCounts
     * @deprecated
     * @param $selector
     * @param $filter
     * @return string
     */
    public function getRewritesQuery($selector, $filter)
    {
        $query = 'SELECT ' . $selector;
        $query .= ' FROM `core_url_rewrite` WHERE ';
        $query .= $filter;
        return $query;
    }

}