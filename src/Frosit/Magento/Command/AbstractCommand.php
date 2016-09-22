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
 * @todo test config overrides
 * @todo add new resource helper
 */

namespace Frosit\Magento\Command;

use Symfony\Component\Console\Input\InputOption;
use N98\Util\Console\Helper\TwigHelper;
use Frosit\Utils\Mysql\MysqliDb;
use N98\Magento\Command\AbstractMagentoCommand;
use Frosit\Utils\Utils;


abstract class AbstractCommand extends AbstractMagentoCommand
{

    /**
     * Configurations from Yaml files
     * @var
     */
    private $_rewriteToolsConfig;

    /**
     * Runtime configurations per command
     * @var
     */
    protected $_rewriteToolsCommand;

    protected $_input;
    protected $_output;

    /**
     * For measuring command runtime
     * @var
     */
    protected $cmdStart;

    public $utils;


    /**
     * constructor to run at every command
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultOptions();
        if (!$this->cmdStart) {
            $this->cmdStart = new \DateTime();
        }
    }

    /**
     * Set default options for all commands in bundle
     */
    private function setDefaultOptions()
    {
        $this->addOption('log-statistics', 'logstats', InputOption::VALUE_NONE, 'Saves statistics as json in the var directory.');
        $this->addOption('share-statistics', 'sharestats', InputOption::VALUE_NONE, 'Shares the stats for research to a specified endpoint.');
    }

    /**
     * @return mixed
     */
    public function getUtils()
    {
        if (null === $this->utils) {
            $this->_curl = new Utils();
        }
        return $this->utils;
    }

    /**
     * @param Utils $utils
     * @return $this
     */
    public function setUtils(Utils $utils)
    {
        $this->utils = $utils;
        return $this;
    }

    /**
     * Gets the RewriteToolsCommand Configuration
     * @note could extend the param to get a value one layer deeper if needed
     * @param bool $getValue
     * @return bool
     */
    protected function getRewriteToolsCommandConfig($getValue = false)
    {
        if (null === $this->_rewriteToolsCommand['config']) {
            $config = $this->setRewriteToolsCommandConfig($this->getCommandConfig());
            if ($config) {
                return $config;
            } else {
                $this->log('Requested rewriteToolsCommand Configuration was not set.');
                return false;
            }

        } else {
            return $getValue ? $this->_rewriteToolsCommand['config'][$getValue] : $this->_rewriteToolsCommand['config'];
        }
    }

    /**
     * Saves configurations for Rewrite Tools Commands
     * @param $config
     */
    protected function setRewriteToolsCommandConfig($config)
    {
        $this->_rewriteToolsCommand['name'] = end(array_reverse(explode(" ", str_replace(":", "_", $this->getSynopsis()))));
        $this->_rewriteToolsCommand['config'] = $config;
    }

    /**
     * Gets the Rewrite Tools config
     * @param bool $value
     * @return mixed
     */
    protected function getRewriteToolsConfig($value = false)
    {
        if (null === $this->_rewriteToolsConfig) {
            $this->setRewriteToolsConfig();
        }
        if ($value) {
            return $this->_rewriteToolsConfig[$value];
        } else {
            return $this->_rewriteToolsConfig;
        }
    }

    /**
     * Sets config from the rewrite tools yaml files
     * @param string $section
     */
    private function setRewriteToolsConfig($section = 'rewriteTools')
    {
        $config = $this->getApplication()->getConfig();
        $this->_rewriteToolsConfig = $config[$section];
    }

    /**
     * Output info message
     * @param $message
     * @return $this
     */
    protected function _info($message)
    {
        $this->_output->writeln("<info>$message</info>");
        return $this;
    }

    /**
     * Output error message
     * @param $message
     * @param bool $log
     * @return $this
     */
    protected function _error($message, $log = true)
    {
        if ($log) {
            $this->log($message);
        }
        $this->_output->writeln("<error>$message</error>");
        return $this;
    }

    /**
     * Logging
     * @todo add command context info
     * @param $message
     * @param bool $logFile
     */
    private function log($message, $logFile = false)
    {
        $logDir = $this->getRewritesDataDir('log');
        if (!is_dir($logDir)) {
            mkdir($logDir);
            chmod($logDir, 0750);
        }
        if (!$logFile) {
            $logFile = "command.log";
        }
        $logFile = $logDir . DS . $logFile;
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
            chmod($logFile, 0640);
        }
        file_put_contents($logFile, $message . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Gets the Rewrites Toolset directory.
     * @note Here we store all files for the Rewrites Toolset
     * @param bool $subdirectory
     * @internal param bool $directory
     * @return string
     */
    protected function getRewritesDataDir($subdirectory = false)
    {
        $directory = $this->_magentoRootFolder . DS . 'var' . DS . $this->getRewriteToolsConfig('dataDir');
        if ($subdirectory) {
            if (!is_dir($directory)) {
                self::getRewritesDataDir();
            }
            $directory .= DS . $subdirectory;
        }
        if (!is_dir($directory)) {
            mkdir($directory);
            chmod($directory, 0750);
        }
        return $directory;
    }

    /**
     * Saves resulting statistics in JSON format
     * @todo auto identify command
     * @todo auto prepend command identification
     * @param $statistics
     * @return bool|string
     * @internal param $data
     */
    public function saveStatisticsAsJson($statistics)
    {
        $filename = $this->getRewritesDataDir('stats') . DS . $this->_rewriteToolsCommand['name'] . '-' . time() . ".json";
        $jsonVars = $this->prepareStatistics($statistics);
        if (file_put_contents($filename, json_encode($jsonVars))) {
            return $filename;
        } else {
            $this->log("Could not save statistics at " . $filename);
            return false;
        }
    }

    /**
     * Generates a twig HTML report
     * @param $statistics
     * @return bool
     */
    public function generateHtmlReport($statistics)
    {
        $filename = $this->getRewritesDataDir('reports') . DS . $this->_rewriteToolsCommand['name'] . '-' . time() . ".html";
        $twigFile = $this->getRewriteToolsCommandConfig('twig');
        $twigVars = array(
            "date" => date('Y-m-d H:i:s T', time()),
            "command" => $this->getRewriteToolsCommandConfig(),
            "statistics" => $statistics
        );

        /** @var $twigHelper TwigHelper */
        $twigHelper = $this->getHelper('twig');
        $buffer = $twigHelper->render($twigFile, $twigVars);
        if (file_put_contents($filename, $buffer)) {
            return true;
        } else {
            $this->log('Something went wrong rendering the twig template and storing it.');
            return false;
        }
    }

    /**
     * Prepare statistics data
     * @todo the shop uid is always the same at hypernode, change this
     * @todo make these statistics more user friendly
     * @param $statistics
     * @return array
     */
    public function prepareStatistics($statistics, $preEncode = false)
    {
        $cmdEnd = new \DateTime();
        $app = $this->getApplication();
        $stats = array(
            'shop_uid' => md5($app->getMagentoRootFolder()),
            'command' => $this->getName(),
            "cmd_start" => $this->cmdStart->getTimestamp(),
            "cmd_end" => $cmdEnd->getTimestamp(),
            "cmd_config" => $this->_rewriteToolsCommand,
            "store_config" => [
                "magento_version" => \Mage::getVersionInfo(),
                "php_version" => phpversion(),
            ],
            "statistics" => $statistics,
            "created" => date('Y-m-d H:i:s', time())
        );
        if ($preEncode) {
            $stats['store_config'] = json_encode($stats['store_config']);
            $stats['statistics'] = json_encode($stats['statistics']);
        }
        return $stats;
    }

    /**
     * Share statistics with a predefined API or Database
     * @todo test with new json format
     * @param $statistics
     * @return bool|AbstractCommand
     */
    public function shareStatistics($statistics)
    {
        if ($dbCredentials = $this->getRewriteToolsConfig('db')['enabled']) {
            $this->saveStatsToDb($statistics);
        }
        if ($this->getRewriteToolsConfig('api')['enabled']) {
            $apiConfig = $this->getRewriteToolsConfig('api');
            $curl = $this->getCurl();

            $stats = $this->prepareStatistics($statistics);

            $curl->post($apiConfig['host'], $stats, true);
            if ($curl->httpStatusCode == 200) {
                return $this->_info('stats hares');
            } else {
                $this->log('Something went wrong while sharing statistics');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Save statistics to defined database
     * @param $statistics
     * @return bool
     */
    public function saveStatsToDb($statistics)
    {
        $dbCredentials = $this->getRewriteToolsConfig('db');
        if ($dbCredentials['enabled']) {
            $db = new MysqliDb($dbCredentials['dbhost'], $dbCredentials['dbuser'], $dbCredentials['dbpass'], $dbCredentials['dbname']);
            $stats = $this->prepareStatistics($statistics, true);

            $id = $db->insert('stats', $stats);
            if ($id) {
                $this->_info('Data inserted in Database, returned id ' . $id);
                return $statistics;
            } else {
                $this->_error('Failed inserting data in database: ' . $db->getLastError());
                return false;
            }
        }
        return false;
    }

    /**
     * Executes some data & reporting functions that are always the same
     * @param $statistics
     */
    public function processCommandEnd($statistics)
    {
        // saving stats
        if ($this->_input->getOption('logstats')) {
            $this->saveStatisticsAsJson($statistics);
        }

        // generating report
        if ($this->_input->getOption('save')) {
            if ($this->generateHtmlReport($statistics)) {
                $this->_info('saved the report');
            } else {
                $this->_error('report could not be saved or generated');
            }
        }

        // Share with API
        if ($this->_input->getOption('sharestats')) {
            if ($this->shareStatistics($statistics)) {
                $this->_info('Statistics were succesfully transmitted');
            } else {
                $this->_error('Something went wrong while sharing the statisctics');
            }
        }
    }

}