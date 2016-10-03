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

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class IndexerCommand
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class IndexerCommand extends AbstractBenchmarkCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:benchmark:indexer')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Amount of reindex actions', 10)
            ->addOption('microtime', 'm', InputOption::VALUE_NONE, 'Display times in microtime')
            ->setDescription('Runs the indexer a couple of times to measure increase in url rewrites.');
    }

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

        // limit
        $limit = $input->getOption('limit') > 0 ? $input->getOption('limit') : false;
        $microtime = $input->getOption('microtime') ? $input->getOption('microtime') : false;

        if ($limit) { // prevent infinite index

            // base values
            $statistics = array();
            $initialRows = $this->countRows($output);
            $progress = new ProgressBar($output, $limit);
            $progress->setOverwrite(true);
            $progress->setFormat(" \n %message%\n\n  <info>%current%/%max%</info> [%bar%] <comment> %percent:3s%% - %elapsed:6s%/%estimated:-6s%  %memory:6s% </comment>");
            $progress->setMessage("<comment>Starting <info>" . $limit . "</info> benchmarks at <info>" . $initialRows . " rows</info> in the core_url_rewrite table.</comment>");
            $progress->start();

            for ($i = 0; $i < $limit; $i++) {

                $startRows = $this->countRows($output);
                $indexResults['run'] = $i;
                $indexResults['runtime'] = $this->executeReIndex($microtime);
                $indexResults['total_rows'] = $this->countRows($output);
                $indexResults['new_rows'] = $indexResults['total_rows'] - $startRows;
                array_push($statistics, $indexResults);

                $progress->clear();
                $progress->setMessage("<info>Completed reindex: (<comment>" . ($i + 1) . "/" . $limit . "</comment>) - (Runtime: <comment>" . $indexResults['runtime'] . "s)</comment> (New rows: <comment>" . $indexResults['new_rows'] . "</comment>)</info>");
                $progress->display();
                $progress->advance();
            }

            $progress->finish();
            $progress->clear();


            // reporting section
            $this->writeSection($output, 'Core URL Rewrite Benchmark. [FROSIT]');

            $tableHelper = $this->getHelper('table');
            $tableHelper->setHeaders(array("#", "Run time", "Total rows", "New rows"));
            $tableHelper->renderByFormat($output, $statistics);

            //finish
            $this->processCommandEnd($statistics);

            return;
        } else {
            $this->_error('The limit is invalid.');
            return;
        }
    }

    /**
     * Counts row in URL Rewrite table
     * @todo optimize
     * @param OutputInterface $output
     * @return int
     */
    protected function countRows(OutputInterface $output)
    {
        $query = "SELECT COUNT(url_rewrite_id) FROM core_url_rewrite";
        $dbHelper = $this->getHelper('database');
        $dbHelper->detectDbSettings($output);
        $db = $dbHelper->getConnection();
        $stmt = $db->query($query);
        $result = 0;
        foreach ($stmt as $item) {
            $result = $item[0];
        }
        return $result;
    }


    /**
     * Execute the url_rewrite reindex action
     * @return int|null
     */
    protected function executeReIndex($microtime = false)
    {
        $indexCode = "catalog_url";
        $this->disableObservers();

        try {
            \Mage::dispatchEvent('shell_reindex_init_process');
            $process = $this->_getIndexerModel()->getProcessByCode($indexCode);
            if (!$process) {
                throw new InvalidArgumentException('Indexer was not found!');
            }

            if(!$microtime){
                $startTime = new \DateTime('now');
            } else {
                $startTime = microtime(true);
            }

            $process->reindexEverything();
            \Mage::dispatchEvent($process->getIndexerCode() . '_shell_reindex_after');

            if(!$microtime){
                $endTime = new \DateTime('now');
                $runtime = $endTime->getTimestamp() - $startTime->getTimestamp();
            } else{
                $endTime = microtime(true);
                $runtime = $endTime - $startTime;
            }


            \Mage::dispatchEvent('shell_reindex_finalize_process');
        } catch (Exception $e) {
            \Mage::dispatchEvent('shell_reindex_finalize_process');
        }
        return isset($runtime) ? $runtime : null;
    }

    /**
     * Gets the URL Rewrite Model
     * @todo maybe make this a global function
     * @return \Mage_Core_Model_Abstract
     */
    protected function getUrlRewriteModel()
    {
        return $this->_getModel('core/url_rewrite', 'Mage_Core_Model_Url_Rewrite');
    }

    /**
     * Gets the indexer Model
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getIndexerModel()
    {
        return $this->_getModel('index/indexer', 'Mage_Index_Model_Indexer');
    }

    /**
     * Disabled observers
     */
    protected function disableObservers()
    {
        $node = \Mage::app()->getConfig()->getNode('adminhtml/events/core_locale_set_locale/observers/bind_locale');
        if ($node) {
            $node->appendChild(new \Varien_Simplexml_Element('<type>disabled</type>'));
        }
    }


}
