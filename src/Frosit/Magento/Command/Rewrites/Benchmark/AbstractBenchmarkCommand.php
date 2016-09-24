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

namespace Frosit\Magento\Command\Rewrites\Benchmark;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;
use Frosit\Utils\UtilsHelper;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractBenchmarkCommand
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
abstract class AbstractBenchmarkCommand extends AbstractRewritesCommand
{

    public $utilsHelper;

    public function __construct()
    {
        parent::__construct();
        $this->setOptions();
    }

    public function processCommandEnd($statistics){
        parent::processCommandEnd($statistics);

        // generating report
        if ($this->_input->getOption('save')) {
            if ($this->generateHtmlReport($statistics)) {
                $this->_info('saved the report');
            } else {
                $this->_error('report could not be saved or generated');
            }
        }
    }

    /**
     * @return UtilsHelper
     */
    public function getUtilsHelper()
    {
        if (null === $this->utilsHelper) {
            $this->utilsHelper = new UtilsHelper();
        }
        return $this->utilsHelper;
    }

    /**
     * Set utils helper
     * @param UtilsHelper $utilsHelper
     * @return $this
     */
    public function setUtilsHelper(UtilsHelper $utilsHelper)
    {
        $this->utilsHelper = $utilsHelper;
        return $this;
    }

    /**
     * Set default options across commands
     */
    private function setOptions()
    {
        $this->addOption('save', null, InputOption::VALUE_NONE, 'Saves the analysis results in a HTML file.');
    }

}
