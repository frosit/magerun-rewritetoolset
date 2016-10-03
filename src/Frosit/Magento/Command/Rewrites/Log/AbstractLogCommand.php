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
 * @todo finish platform and server recognition
 * @todo move url ignore patterns to config
 *
 */

namespace Frosit\Magento\Command\Rewrites\Log;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractLogCommand
 * @package Frosit\Magento\Command\Rewrites\Log
 */
abstract class AbstractLogCommand extends AbstractRewritesCommand
{
    public $platform;
    public $webserver;

    /**
     * AbstractAnalysisCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setOptions();
    }

    public function setEnvironment()
    {
        $this->identifyPlatform();
    }

    public function processCommandEnd($statistics)
    {
        parent::processCommandEnd($statistics);
    }

    /**
     * Set default options for all analysis commands
     */
    private function setOptions()
    {
        $this->addOption('save', null, InputOption::VALUE_NONE, 'Saves the analysis results in a HTML file.');
    }

    /**
     * @return $this
     */
    public function identifyPlatform()
    {
        if (OperatingSystem::isProgramInstalled('hypernode-ftp')) {
            $this->platform = "hypernode";
            $this->webserver = "nginx";
        } else {
            $this->platform = PHP_OS;
            if (OperatingSystem::isProgramInstalled('apachectl')) {
                $this->webserver = "apache";
            } elseif (OperatingSystem::isProgramInstalled('nginx')) {
                $this->webserver = "nginx";
            }
        }
        return $this;
    }

    /**
     * Convert to bytes
     * @param $from
     * @return string
     */
    public function convertToBytes($from)
    {
        $number = substr($from, 0, -1);
        switch (strtoupper(substr($from, -1))) {
            case "K":
                return $number * 1024;
            case "M":
                return $number * pow(1024, 2);
            case "G":
                return $number * pow(1024, 3);
            default:
                return $from;
        }
    }

    /**
     * Bytes to human readable
     * @todo make less dirty
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    public function human_filesize($bytes, $decimals = 2)
    {
        $size = array('B', 'kiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    /**
     * Detects file compression
     * @param $file
     * @return bool|mixed|string
     */
    public function getFileCompression($file)
    {
        $type = false;
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);
        } catch (\Exception $e) {
            $this->_error("Could not determine file MIME type, using file name instead");
        }

        // find by filename
        if (!$type) {
            if (strpos($file, ".gz")) {
                $type = "application/x-gzip";
            }
        }

        // else it should be plain or unknown
        if ($type == "text/plain") {
            $type = false;
        }

        // make shortcut
        switch ($type) {
            case "application/x-gzip":
                $type = "gz";
                break;
            case "application/x-bzip2":
                $type = "bz";
                break;
            default:
                $type = false;
        }
        return $type;
    }


    /**
     * Fetches logs from configuration
     * @todo test
     * @return bool
     */
    public function getLogsFromConfig()
    {
        $cmdConfig = $this->getCommandConfig();
        if ($webserver = $this->webserver) {
            return $cmdConfig[$this->webserver . '_access_logs'];
        } else {
            $this->_error("Where are the access logs? Specify them using --file, optionally use ssh cmd locate access.log");
            return false;
        }
    }

}