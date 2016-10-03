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
 * @todo bzip does work, never saw bzip logs anyway
 * @note not sure if i or the user should make sure the logs are not too old, extra complexity <-> performance
 * @note no windows support, ever
 */

namespace Frosit\Magento\Command\Rewrites\Log;

use Frosit\Utils\MicroDB\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use N98\Util\Exec;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class ParseCommand
 * @package Frosit\Magento\Command\Rewrites\Log
 */
class ParseCommand extends AbstractLogCommand
{

    protected $clean;
    protected $dbPath;

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:log:parse')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, "path to the log file(s), quote the string while using an asterisk")
            ->addOption('to-db', null, InputOption::VALUE_OPTIONAL, "Database id to overwrite")
            ->addOption('clean', null, InputOption::VALUE_OPTIONAL, "Instantly cleans out invalid Urls (format / time), takes more time, requires less memory", true)
            ->addOption('webserver', null, InputOption::VALUE_OPTIONAL)
            ->addOption('platform', null, InputOption::VALUE_OPTIONAL)
            ->setHelp('This command parses apache and nginx access logs so they can be whitelisted in rewrite cleaning while maintaining SEO scores. The command is self-aware of it\'s memory usage and will clean out some garbage at 80% ')
            ->setDescription('Parses access logs for whitelist URL\'s');
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
            $this->setEnvironment();
        }
        $this->writeSection($output, 'Rewrite access log parser  [FROSIT]');

        if ($webserver = $input->getOption('webserver')) {
            $this->webserver = $webserver;
        }
        if ($platform = $input->getOption('platform')) {
            $this->platform = $platform;
        }
        $this->clean = $input->getOption('clean');
        if (!$logs = $input->getOption('file')) {
            $logs = $this->getLogsFromConfig();
        }

        $uniqueLogLines = $this->parseLogs($logs);

        if ($uniqueLogLines) {
            $dbpath = $this->dbPath = $this->getDataDir() . '/' . 'log'; //@todo make configurable
            $db = new Database($dbpath);
            if ($selectedDb = $input->getOption('to-db')) { //@todo test
                $dbpath .= "/" . $selectedDb;
            }
            if ($db->exists($selectedDb)) {
                $db->save($selectedDb, $uniqueLogLines);
                $this->_info("Urls overwritten in Database " . $selectedDb);
            } else {
                $id = $db->create($uniqueLogLines);
                if ($id) {
                    $this->_info("created database with id <comment>" . $id . "</comment>");
                    $this->_info("The Log URLs are saved within <comment>" . $dbpath . "</comment>");
                } else {
                    $this->_error("Something went wrong while saving.");
                }
            }
        } else {
            $this->_error("There was no data returned.");
        }
    }

    /**
     * Create array of logs from input
     * @note input must be quoted to glob
     * @param $logs
     * @return array
     */
    public function createLogsArray($logs)
    {
        $logFiles = array();
        if (!is_array($logs)) {
            if (strpos($logs, "*")) {
                $logFiles = glob($logs);
            } elseif (file_exists($logs)) {
                $logFiles[] = $logs;
            }
        }
        return $logFiles;
    }


    /**
     * Counts lines in log file
     * @todo test the isset / null and cmd output
     * @param $logFile
     * @return bool|null
     */
    public function countLines($logFile, $c)
    {
        $canWC = OperatingSystem::isProgramInstalled('wc');
        if ($canWC) {
            if ($c) {
                $c = $c . "cat";
            } else {
                $c = "cat";
            }
            if (OperatingSystem::isProgramInstalled($c)) {
                $returnValue = null;
                $commandOutput = null;
                Exec::run($c . " " . (string)$logFile . " | wc -l && clear", $commandOutput, $returnValue);
                if ($lines = intval(str_replace("\n", "", $commandOutput))) {
                    $this->_info($lines);
                }
            }
        }
        return isset($commandOutput) ? $commandOutput : false;
    }

    /**
     * Parses log files
     * @param $logs
     * @return array
     */
    public function parseLogs($logs)
    {
        $results = array();
        $logFiles = $this->createLogsArray($logs);

        // @todo add confirmation
        $li = 1;
        foreach ($logFiles as $logFile) {
            $lines = array();

            $this->_info("Starting log " . $li . "/" . count($logFiles));
            $compression = $this->getFileCompression($logFile);

            $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
            $initialMemoryUsage = memory_get_usage();

            if ($totalLines = $this->countLines($logFile, $compression)) {
                $progress = new ProgressBar($this->_output, $totalLines / 100);
                $progress->setFormat("[%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%" . " / " . ini_get('memory_limit') . ":%message% ");
                $progress->setMessage("Started...");
                $progress->start();
            }

            // Doing the while loops
            $i = 0;
            if ($handle = $this->logOpen($compression, $logFile, "r")) {
                while (($buffer = $this->logGets($compression, $handle, 4096)) !== false) {

                    // === can add more options for log parsing here
                    $url = $this->parseLogString($buffer);
                    if ($this->clean) {
                        if ($url = $this->validateUrl($url)) {
                            $lines[] = $url;
                        }
                    } else {
                        $lines[] = $url;
                    }

                    // Progress and memory management
                    if ($i >= 100) {
                        $mp = (round((memory_get_usage() + $initialMemoryUsage) / $memoryLimit * 100));
                        if ($mp >= 80) {
                            $lines = array_unique($lines);
                            gc_collect_cycles();
                        }
                        if (isset($progress)) {
                            $progress->clear();
                            $progress->setMessage($mp . "%");
                            $progress->display();
                            $progress->advance();
                        }
                        $i = 0;
                    } else {
                        $i++;
                    }

                }
                if (!$this->logEof($compression, $handle)) {
                    $this->_error("Err: unexpected end.");
                }
                $this->logClose($compression, $handle);
            }

            // end
            if (isset($progress)) {
                $progress->finish();
            }

            $lines = array_unique($lines);
            $this->_info("Finished log " . $li . "/" . count($logFiles) . " found <comment>" . count($lines) . "</comment> unique lines in this log.");

            $results = array_merge($results, $lines);
            $results = array_unique($results);
            $li++;
        }

        $results = array_unique($results);
        $this->_info("Finished parsing <comment>" . count($logFiles) . "</comment> logs, found <comment>" . count($results) . "</comment> unique lines in all logs.");
        return $results;
    }

    /**
     * Parse log string
     * @todo finish apache log parsing
     * @param $string
     * @return mixed
     */
    public function parseLogString($string)
    {
        if ($this->platform == "hypernode") {
            return explode(" ", json_decode($string)->request)[1];
        } elseif ($this->webserver == "apache") {

            $this->_error("Sorry! I did not finish Apache log parsing yet..., exiting");
            exit();

            $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';
            preg_match($regex, $string, $matches);
            return $matches[4]; //@todo finish
        } else {
            $this->_error("can not parse the log line due to platform or webserver recognition");
            die();
        }
    }

    /**
     * Open log file
     * @param $c
     * @param $logFile
     * @param $mode
     * @return resource
     */
    public function logOpen($c, $logFile, $mode)
    {
        switch ($c) {
            case "gz":
                return gzopen($logFile, $mode);
                break;
            case "bz":
                return bzopen($logFile, $mode);
                break;
            default:
                return fopen($logFile, $mode);
                break;
        }
    }

    /**
     * Get line from log
     * @param $c
     * @param $handle
     * @param $length
     * @return string
     */
    public function logGets($c, $handle, $length)
    {
        switch ($c) {
            case "gz":
                return gzgets($handle, $length);
                break;
            case "bz":
                return bzread($handle, $length);
                break;
            default:
                return fgets($handle, $length);
                break;
        }
    }

    /**
     * If end of file
     * @param $c
     * @param $handle
     * @return array|bool|int
     */
    public function logEof($c, $handle)
    {
        switch ($c) {
            case "gz":
                return gzeof($handle);
                break;
            case "bz":
                return bzerror($handle);
                break;
            default:
                return feof($handle);
                break;
        }
    }

    /**
     * Close stream
     * @param $c
     * @param $handle
     * @return bool|int
     */
    public function logClose($c, $handle)
    {
        switch ($c) {
            case "gz":
                return gzclose($handle);
                break;
            case "bz":
                return bzclose($handle);
                break;
            default:
                return fclose($handle);
                break;
        }
    }


}