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
 * @todo de-bloat this
 */

namespace Frosit\Magento\Command\Rewrites\Benchmark;

use Frosit\Utils\UtilsHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class SitePerformanceCommand
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class SitePerformanceCommand extends AbstractBenchmarkCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:benchmark:site-performance')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limits the URLs to crawl', 100)
            ->addOption('noProducts', null, InputOption::VALUE_NONE, 'Skips product URLs')
            ->addOption('noCategories', null, InputOption::VALUE_NONE, 'Skips Category URLs')
            ->addOption('noCms', null, InputOption::VALUE_NONE, 'Skips CMS urls')
            ->addOption('warm-cache', null, InputOption::VALUE_NONE, 'Quickly warms the cache by very fast multicurling')
            ->addOption('no-mc', null, InputOption::VALUE_NONE, 'Uses regular curling instead of multi, rate limits...')
            ->addOption('random', null, InputOption::VALUE_NONE, 'Randomizes URls, works good with limit for some diversity.')
            ->addOption('to-sitemap', null, InputOption::VALUE_NONE, 'Saves the loaded URL in a sitemap.')
            ->setDescription('Benchmarks site performance by crawling the registered urls.');
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
            return false;
        } else {
            $this->_input = $input;
            $this->_output = $output;
            $this->setRewriteToolsCommandConfig($this->getCommandConfig());
        }

        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        $statistics = array();

        // header
        $this->writeSection($output, 'Core URL Rewrite Benchmarks. [FROSIT]');

        // url class
        $config = $this->prepareConfig($input);
        $sitemap = $input->getOption('to-sitemap') ? $input->getOption('to-sitemap') : false;

        $urls = $this->getUtilsHelper()->getMagentoUrls()->getUrls($stores, $config, $sitemap);

        if ($input->getOption('warm-cache')) {
            if ($input->getOption('no-mc')) {
                $i = 0;
                foreach ($urls as $url) {
                    $res = $this->simpleCurl($url);
                    $this->_info("(cachewarm) Warmed " . $i . "/" . count($urls) . " : <comment>" . $url . "</comment> - Status: <comment>" . $res['response_code'] . "</comment> Time: <comment>" . $res['time'] . "</comment>");
                    $i++;
                }
                echo PHP_EOL;
                echo PHP_EOL;
            } else {
                $this->multicurl($urls);
            }
        }

        $progress = new ProgressBar($output, count($urls));
        $progress->setOverwrite(true);
        $progress->setFormat(" \n %message%\n\n  <info>%current%/%max%</info> [%bar%] <comment> %percent:3s%% - %elapsed:6s%/%estimated:-6s%  %memory:6s% </comment>");
        $progress->setMessage('Starting performance test');
        $progress->start();

        foreach ($urls as $url) {

            $statistics[] = $stats = $this->simpleCurl($url);

            $progress->clear();
            $progress->setMessage("<info>URL: <comment>" . $url . "</comment> Status: <comment>" . $stats['response_code'] . "</comment> Time: " . $stats['time'] . "</info>");
            $progress->display();
            $progress->advance();
        }

        $progress->finish();
        $progress->clear();

        // table
        $tableHelper = $this->getHelper('table');
        $tableHelper->setHeaders(array("URL", "Status", "Time"));
        $tableHelper->renderByFormat($output, $statistics);

        // fi
        $endResult = $this->processCommandEnd($statistics);

        return $endResult;
    }

    /**
     * Simple curl function
     * @param $url
     * @return array
     */
    public function simpleCurl($url)
    {
        $curl = $this->getUtilsHelper()->getCurl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $curl->get($url);

        $stats = array(
            "url" => $url,
            "response_code" => $curl->getInfo(CURLINFO_HTTP_CODE),
            "time" => $curl->getInfo(CURLINFO_STARTTRANSFER_TIME),
        );
        return $stats;
    }

    /**
     * Fast multicurl for warming up the cache
     * @todo return data from $instance
     * @param $urls
     */
    public function multicurl($urls)
    {
        $multicurl = $this->getUtilsHelper()->getMultiCurl();
        $chunks = array_chunk($urls, 25);
        foreach ($chunks as $chunk) {
            $multicurl->success(function ($instance) {
                $this->_info("call to <comment>" . $instance->url . "</comment> was <comment>successful.</comment>");
            });
            $multicurl->error(function ($instance) {
                $this->_error('call to "' . $instance->url . '" was unsuccessful. code:' . $instance->errorCode, false);
            });
            $multicurl->complete(function ($instance) {
                $this->_info('call completed');
            });
            foreach ($chunk as $item) {
                $multicurl->addGet($item);
            }
            $multicurl->start();
        }
    }

    /**
     * Prepares the url resolve configuration
     * @param InputInterface $input
     * @return array
     */
    public function prepareConfig(InputInterface $input)
    {
        $config = [
            "random" => false,
            "products" => false, // @note, the input var is "noProducts"
            "categories" => false,
            "cms" => false
        ];
        // get urls
        if ($input->getOption('random')) {
            $config['random'] = true;
        }
        if ($input->getOption('noProducts')) {
            $config['products'] = true;
        }
        if ($input->getOption('noCategories')) {
            $config['categories'] = true;
        }
        if ($input->getOption('noCms')) {
            $config['cms'] = true;
        }

        if ($input->getOption('limit')) {
            $config['limit'] = $input->getOption('limit');
        }

        return $config;
    }

}