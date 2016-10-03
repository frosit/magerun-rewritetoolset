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

use Frosit\Utils\Csv\parseCSV;
use Frosit\Utils\MicroDB\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CsvCommand
 * @package Frosit\Magento\Command\Rewrites\Url
 */
class CsvCommand extends AbstractUrlCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:url:csv')
            ->addOption('csv', null, InputOption::VALUE_OPTIONAL, 'path to csv')
            ->addOption('column', null, InputOption::VALUE_OPTIONAL, 'name of column containing urls to whitelist')
            ->setDescription('Import whitelist url\'s from CSV, for example, google analytics');
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

        $urls = array();

        if ($input->getOption('csv') && $input->getOption('column')) {
            $rankedUrls = $this->getUrlsFromCsv($input->getOption('csv'), $input->getOption('column'));
            if ($rankedUrls) {
                $urls = array_merge($urls, $rankedUrls);
            }
        }

        $urls = array_unique($urls);

        $this->_info("Found <comment>" . count($urls) . "</comment> unique URLs for whitelisting.");

        $dbpath = $this->dbPath = $this->getDataDir() . '/' . 'csv'; //@todo make configurable
        $mdb = new Database($dbpath);

        if ($mdb->create($urls)) {
            $this->_info("Imported URL's sucessfully saved in <comment>" . $dbpath . " </comment>");
        };
    }

    /**
     * Fetches URLs from CSV
     * @param $filename
     * @param $column
     * @return array|bool
     */
    public function getUrlsFromCsv($filename, $column)
    {
        $file = $this->_magentoRootFolder . DS . $filename;
        if (file_exists($file)) {
            $csv = new parseCSV();
            $csv->auto($file);
            $urlList = array_column($csv->data, $column);
            if ($urlList) {
                $cleanUrls = $this->cleanUrls($urlList); // @todo clean can maybe replaced for the more extended validate url
                return $cleanUrls;
            } else {
                $this->_error("Could not find any urls using column " . $column . " in file " . $filename);
                return false;
            }
        } else {
            $this->_error("File " . $filename . "Does not seem to exist.");
            return false;
        }
    }

    /**
     * Cleans out bad URLs
     * @param $urls
     * @return array
     */
    public function cleanUrls($urls)
    {
        $cleanUrls = array();
        foreach ($urls as $url) {
            $badUrl = false;
            if (strpos($url, "?")) {
                continue;
            }
            if ($url == "") {
                continue;
            }
            $url = ltrim($url, "/");
            if (!$badUrl) {
                $cleanUrls[] = $url;
            }
        }
        return $cleanUrls;
    }
}