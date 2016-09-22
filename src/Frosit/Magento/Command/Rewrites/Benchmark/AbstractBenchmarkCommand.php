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
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractBenchmarkCommand
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
abstract class AbstractBenchmarkCommand extends AbstractRewritesCommand
{

    public function __construct()
    {
        parent::__construct();

        $this->setOptions();
    }

    /**
     * Set default options across commands
     */
    private function setOptions()
    {
        $this->addOption('save', null, InputOption::VALUE_NONE, 'Saves the analysis results in a HTML file.');
    }

}
