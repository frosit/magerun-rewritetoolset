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


namespace Frosit\Magento\Command\Rewrites\Clean;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class YoloCommand
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
class YoloCommand extends AbstractCleanCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:clean:yolo')
            ->setDescription('Remove all dupes without giving a !@# about SEO. [experimental]');
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
        $this->writeSection($output, 'Rewrite Clean. [FROSIT]');

        // ask some user confirm
        $helper = $this->getHelper('question');
        if (!$input->getOption('dry-run')) {
            $question = new ConfirmationQuestion("<error>WARNING</error> <info>Removing rewrites may cause <comment>negative SEO</comment> and <comment>404's</comment> but </info><comment>improves performance!</comment> <question>Yolo?</question><comment> [Y/n]</comment>", false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        // start cleaning loop
        // @todo add process and make big db proof
        foreach ($stores as $store) {
            if ($store == 0) {
                if (count($stores) == 1) {
                    $output->writeln('<error>Do not mess with the admin store, which is the only selected</error>');
                }
                continue;
            }

            $query = '';
            if ($input->getOption('dry-run')) {
                $query .= "SELECT COUNT(*) ";
            } else {
                $query .= "DELETE ";
            }
            $query .= "FROM `core_url_rewrite` WHERE `store_id` = " . $store['store_id'] . " AND `is_system` = 0 AND `options` = 'RP' ";
            $connection = $this->getConnection();

            if ($input->getOption('dry-run')) {
                $result = $connection->fetchOne($query);
            } else {
                $result = $connection->query($query)->rowCount();
            }

            $output->writeln('<info>Removed <comment>' . $result . '</comment> rows for store <comment>' . $store['name'] . '</comment> from the database.</info>');

            $statistics[] = array($store,"removed_rows"=> $result);
        }

        $this->processCommandEnd($statistics);
    }
}