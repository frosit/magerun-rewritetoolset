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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HistoryCommand
 * @package Frosit\Magento\Command\Rewrites\Analysis
 */
class HistoryCommand extends AbstractAnalysisCommand
{

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:analysis:history')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'limits the query, use in case of large tables or timeouts', false)
            ->setDescription('Calculates a timeline of created duplicate rewrites.');
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
        $this->writeSection($output, 'Rewrite statistics - History [FROSIT]');

        // stores
        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        // stats
        $statistics = $this->gatherStatistics($stores, $input->getOption('limit'));
        $statistics = $this->prepareTableData($statistics);

        // table
        $tableHelper = $this->getHelper('table');
        $tableHelper->setHeaders(array("Date", "Created Duplicates"));
        $tableHelper->renderByFormat($output, $statistics);

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
        $tableData = array();
        $format = 'Y-m-d H:i:s T';
        foreach ($statistics as $key => $value) {
            $tableData[] = array("date" => date($format, $key), "duplicates" => count($value));
        }
        return $tableData;
    }

    /**
     * Gathers the statistics by executing the query's
     * @todo optimize
     * @param $stores
     * @param $limit
     * @return array
     */
    public function gatherStatistics($stores, $limit)
    {
        $statistics = array();
        $connection = $this->getConnection();

        foreach ($stores as $store) {
            $query = 'SELECT `id_path` FROM `core_url_rewrite` WHERE `is_system` = 0 ';
            $query .= 'AND `store_id` = ' . $store['store_id'];
            if ($limit) {
                $query .= " LIMIT " . $limit;
            }
            $rewrites = $connection->fetchAll($query);
            foreach ($rewrites as $rewrite) {
                $timestamp = end(explode("_", $rewrite['id_path']));
                $statistics[$timestamp][] = $rewrite;
            }
        }
        ksort($statistics);
        return $statistics;
    }

}