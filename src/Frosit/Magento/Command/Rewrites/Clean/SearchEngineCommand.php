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

use Frosit\Utils\Csv\parseCSV;
use Frosit\Utils\UtilsHelper;
use N98\Magento\DbSettings;
use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class YoloCommand
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
class SearchEngineCommand extends AbstractCleanCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:clean:search')
            ->addOption('csv', null, InputOption::VALUE_OPTIONAL, 'path to csv')
            ->addOption('column', null, InputOption::VALUE_OPTIONAL, 'name of column')
            ->addOption('logged-urls')
            ->setDescription('Command in development');
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
        $urls = array();

        if ($input->getOption('csv') && $input->getOption('column')) {
            $rankedUrls = $this->getUrlsFromCsv($input->getOption('csv'), $input->getOption('column'));
            if ($rankedUrls) {
                $urls = array_merge($urls, $rankedUrls);
            }
        }
        if ($input->getOption('logged-urls')) {
            $loggedUrls = $this->getLoggedUrls();
            if ($loggedUrls) {
                $urls = array_merge($urls, $loggedUrls);
            }
        }

        foreach ($stores as $store) {


        }



        // header
        $this->writeSection($output, 'Rewrite Clean. [FROSIT]');
    }
}