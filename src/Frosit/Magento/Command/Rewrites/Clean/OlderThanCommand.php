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
 * @todo unify database querying
 * @todo filter out user defined rewrites
 * @todo add process
 * @todo test against large db
 */


namespace Frosit\Magento\Command\Rewrites\Clean;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class OlderThanCommand
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
class OlderThanCommand extends AbstractCleanCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:clean:older-than')
            ->addArgument('days', InputArgument::REQUIRED, 'Amount of days the rewrite should be older than now')
            ->setDescription('Remove all dupes older than x days [development]');
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

        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        $statistics = array();

        // header
        $this->writeSection($output, 'Rewrites Clean. [FROSIT]');

        $days = $input->getArgument('days');
        $nowTimestamp = time();
        $thresholdTimestamp = $nowTimestamp - ($days * 86400);
        $thresholdDatetime = date('Y-m-d H:i:s', $thresholdTimestamp);


        $helper = $this->getHelper('question');
        if (!$input->getOption('dry-run')) {
            $question = new ConfirmationQuestion("<error>Caution!</error> Are you sure you want to remove all rewrites older than <comment>" . $thresholdDatetime . " (" . $days . ")</comment> days? <question>Continue?</question><comment> [Y/n]</comment>", false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        // start cleaning loop
        // @todo add process and make big db proof
        foreach ($stores as $store) {
            if ($store == 0) {
                if (count($stores) == 1) {
                    $output->writeln('<error>Select a different store.</error>');
                }
                continue;
            }

            $query = '';
            if ($input->getOption('dry-run')) {
                $query .= "SELECT COUNT(*) ";
            } else {
                $query .= "DELETE ";
            }
            $query .= "FROM `core_url_rewrite` WHERE `store_id` = " . $store['store_id'] . " AND `is_system` = 0 AND `options` = 'RP' AND substring_index(`id_path`, '_', -1) < " . $thresholdTimestamp . " ";
            $connection = $this->getConnection();

            if ($input->getOption('dry-run')) {
                $result = $connection->fetchOne($query);
            } else {
                $result = $connection->query($query)->rowCount();
            }

            $this->_info("Removed <comment>" . $result . "</comment> rows for store <comment>" . $store['name'] . "</comment> from the database.");

            $statistics[] = array($store, "removed_rows" => $result);
        }

        $this->processCommandEnd($statistics);
    }
}