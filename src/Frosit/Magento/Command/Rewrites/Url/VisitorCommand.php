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

use Frosit\Utils\MicroDB\Database;
use Frosit\Utils\Mysql\MysqliDb;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VisitorCommand
 * @package Frosit\Magento\Command\Rewrites\Url
 */
class VisitorCommand extends AbstractUrlCommand
{

    protected function configure()
    {
        $this
            ->setName('rewrites:url:visitor')
            ->addOption('max-age', null, InputOption::VALUE_OPTIONAL, 'maximum age in days for URLs in the visitor log to be whitelisted', 60)
            ->setDescription('Import visitor log Urls');
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

        $maxAge = $input->getOption('max-age');

        $min = (time() - (86400 * $maxAge));
        $db = new MysqliDb($this->getDbCredentials());
        $ids = $db->subQuery();
        $ids->where('visit_time', date('Y-m-d H:i:s T', $min), '>=');
        $ids->get('log_url', null, 'url_id');
        $db->where('url_id', $ids, 'in');
        $res = $db->get('log_url_info', null, 'url');

        $urls = array_unique($res);
        $this->_info("Found <comment>" . count($urls) . "</comment> unique URLs for whitelisting.");

        if(count($urls) >= 1){
            $dbpath = $this->dbPath = $this->getDataDir() . '/' . 'visitor'; //@todo make configurable
            $mdb = new Database($dbpath);

            if ($mdb->create($urls)) {
                $this->_info("Imported URL's sucessfully saved in <comment>".$dbpath." </comment>");
            };
        } else {
            $this->_info("Skipping...");
        }

    }

    /**
     * Gets urls for the visitor log table
     * @return array
     */
    public function getLoggedUrls()
    {
        $db = new MysqliDb($this->getDbCredentials());
        $db->where('visit_time');
        $urlInfo = $db->get('log_url');
        $urls = array_column($urlInfo, 'url');
        return $urls;
    }
}