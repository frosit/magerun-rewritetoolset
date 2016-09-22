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

namespace Frosit\Magento\Command\Rewrites\Analysis;

use Frosit\Magento\Command\Rewrites\AbstractRewritesCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractAnalysisCommand
 * @package Frosit\Magento\Command\Rewrites\Analysis
 */
abstract class AbstractAnalysisCommand extends AbstractRewritesCommand
{

    /**
     * AbstractAnalysisCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setOptions();
    }

    /**
     * Set default options for all analysis commands
     */
    private function setOptions()
    {
        $this->addOption('save', null, InputOption::VALUE_NONE, 'Saves the analysis results in a HTML file.');
    }


}