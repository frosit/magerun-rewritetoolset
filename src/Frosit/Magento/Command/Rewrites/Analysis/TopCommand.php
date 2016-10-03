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

use Frosit\Utils\Mysql\MysqliDb;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TopCommand
 * @package Frosit\Magento\Command\Rewrites\Analysis
 */
class TopCommand extends AbstractAnalysisCommand
{

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:analysis:top')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'limits the amount of top ... for each type', 100)
            ->setDescription('Shows the top ... of most duplicating products / category\'s.');
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

        // stats
        $categoryDupes = $this->gatherStatistics("category_id", $input->getOption('limit'));
        $productDupes = $this->gatherStatistics("product_id", $input->getOption('limit'));

        $statistics = $this->prepareTableData($categoryDupes, $productDupes);
        // table
        $tableHelper = $this->getHelper('table');
        $tableHelper->setHeaders(array("Category ID", "#", "Product ID", "#"));
        $tableHelper->renderByFormat($output, $statistics);


        $this->processCommandEnd($statistics);
    }

    /**
     * Prepares data for table output
     * @param $catStats
     * @param $prodStats
     * @return array
     */
    public function prepareTableData($catStats, $prodStats)
    {
        $tableData = array();
        $max = max(array(count($catStats), count($prodStats)));

        for ($i = 0; $i <= $max; $i++) {
            $tableData[] = array($catStats[$i]['category_id'], $catStats[$i]['magnitude'], $prodStats[$i]['product_id'], $prodStats[$i]['magnitude']);
        }
        return $tableData;
    }

    /**
     * Queries the dabase for statistics
     * @param $entity
     * @param $limit
     * @return array|bool
     * @internal param bool $store
     */
    public function gatherStatistics($entity, $limit)
    {
        $db = new MysqliDb($this->getDbCredentials());
        $query = "
        SELECT `" . $entity . "`, COUNT(*)  AS magnitude 
        FROM `core_url_rewrite`
        WHERE `is_system` = '0'
        AND `" . $entity . "` REGEXP '^[0-9]+$'" . " ";
        $query .=
            " GROUP BY `" . $entity . "` 
        ORDER BY magnitude DESC
        LIMIT " . $limit;

        $dupes = $db->rawQuery($query);
        if ($dupes) {
            return $dupes;
        } else {
            $this->_error($db->getLastError());
            return false;
        }
    }

}