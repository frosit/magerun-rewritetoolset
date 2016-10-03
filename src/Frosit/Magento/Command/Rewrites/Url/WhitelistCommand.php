<?php
/**
 * Frosit magerun RewriteToolset
 *
 * @category    project
 * @package     magerun-RewriteToolset
 * @author      Fabio Ros <info@frosit.nl>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * @todo is the file storage necessary, maybe listen to memory
 */

namespace Frosit\Magento\Command\Rewrites\Url;

use Frosit\Utils\MicroDB\Database;
use Frosit\Utils\Mysql\MysqliDb;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WhitelistCommand
 * @package Frosit\Magento\Command\Rewrites\Url
 */
class WhitelistCommand extends AbstractUrlCommand
{

    protected $maxAge;
    protected $_debug;

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:url:whitelist')
            ->addOption('max-age', null, InputOption::VALUE_OPTIONAL, 'Maximum age in days', 60)
            ->addOption('debug', null, InputOption::VALUE_OPTIONAL, 'Dumps URLs in all steps to the tmp folder')
            ->setDescription('Uses the urls gathered from sources to whitelist rewrites before cleaning');
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

        if ($input->getOption('max-age')) {
            $this->maxAge = $input->getOption('max-age');
        }
        $this->_debug = $input->getOption('debug');

        // header
        $this->writeSection($output, 'Rewrite whitelisting  [FROSIT]');

        // set directories
        $rewritesDataDir = $this->getRewritesDataDir('data');
        $rewritesDataTempDir = $rewritesDataDir . "/tmp/";

        // gather and merge all urls (log parsing, visitor log, Google CSV, etc)
        if ($allUrls = $this->getMergedGatheredUrlsFromDb()) {
            $this->_info("Merged / fetched " . count($allUrls) . " unique urls for whitelisting");
            $allUrls = array_unique($allUrls); // @todo necessary?
            if ($this->_debug) {
                file_put_contents($rewritesDataTempDir . "allparsed.json", json_encode($allUrls)); //@todo
            }

            if ($segments = $this->createQuerySegments($allUrls)) {
                $this->_info("Converted URLs into " . count($segments) . " segments");
                if ($this->_debug) {
                    file_put_contents($rewritesDataTempDir . "segments.json", json_encode($segments)); //@todo
                }

                if ($whitelistRewrites = $this->querySegments($segments, $allUrls)) {

                    if ($this->_debug) {
                        file_put_contents($rewritesDataTempDir . "whitelistedRewrites.json", json_encode($whitelistRewrites)); //@todo
                    }

                    $dbpath = $this->dbPath = $this->getDataDir() . '/' . 'whitelist'; //@todo make configurable
                    $mdb = new Database($dbpath);
                    $mdb->create($whitelistRewrites);

                } else {
                    $this->_error("No rewrites returned for the whitelist, that's okay if your low on dupes.");
                }
            } else {
                $this->_error("For some reason, segments could not be created, verify the formatting");
            }
        } else {
            $this->_error("Could not fetch and or merge previously gathered URL's");
        }


    }

    /**
     * Query the segments to create whitelist url rewrites while keeping the footprint low
     * @todo might need process viewing and memory management
     * @param $segments
     * @param $allUrls
     * @return array
     */
    public function querySegments($segments, $allUrls)
    {
        $mysql = new MysqliDb($this->getDbCredentials());

        $whitelisted = array();

        $tot = 0;
        $i = 0;
        foreach ($segments as $segment) {

            $query = "SELECT `url_rewrite_id`,`store_id`,`id_path`,`request_path` FROM `core_url_rewrite` WHERE `is_system` = '0' AND `request_path` LIKE '" . $segment . "%'";
            $rewrites = $mysql->query($query);

            if ($rewrites) {
                $tot = $tot + count($rewrites);
                foreach ($rewrites as $rewrite) {
                    if ($this->validateRewriteForWhitelist($rewrite)) {

                        // hotfix for logparsed
                        if (substr($rewrite['request_path'], 0, 1) != "/") {
                            $rewrite['request_path'] = "/" . $rewrite['request_path'];
                        }

                        if (in_array($rewrite['request_path'], $allUrls)) {
                            $whitelisted[$rewrite['url_rewrite_id']] = $rewrite['request_path'];
                        }
                    }
                }
            }
            $this->_info("Queried <comment>" . $i . "</comment> of <comment>" . count($segments) . "</comment> segments.");
            $i++;
        }
        $this->_info("Finished querying segments. Added <comment>" . count($whitelisted) . "</comment> of <comment>" . $tot . "</comment> urls to the whitelist.");

        return $whitelisted;
    }

    /**
     * Small max-age validation
     * @param $rewrite
     * @return bool
     */
    public function validateRewriteForWhitelist($rewrite)
    {
        $valid = true;
        $timestamp = end(explode("_", $rewrite['id_path']));
        if (((time() - $timestamp) / 86400) > $this->maxAge) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * Create segments for like queries
     * @param $urls
     * @return array
     */
    public function createQuerySegments($urls)
    {
        $segments = array();
        foreach ($urls as $url) {
            $plorp = substr(strrchr($url, '-'), 1);
            $string = substr($url, 0, -strlen($plorp));
            $string = rtrim($string, "-");
            $string = ltrim($string, "/");
            if (strlen($string) > 0) {
                $segments[] = $string;
            }
        }
        $segments = array_unique($segments);
        asort($segments);
        return $segments;
    }

    /**
     * Fetches DB Credentials
     * @return array
     */
    public function getDbCredentials()
    {
        $config = \Mage::getConfig()->getResourceConnectionConfig("default_setup");
        $credentials = array(
            "host" => (string)$config->host,
            "username" => (string)$config->username,
            "password" => (string)$config->password,
            "db" => (string)$config->dbname
        );
        return $credentials;
    }

}
