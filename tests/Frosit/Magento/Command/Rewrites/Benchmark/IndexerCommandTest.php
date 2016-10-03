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

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class IndexerCommandTest
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class IndexerCommandTest extends testCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new IndexerCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:benchmark:indexer');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:benchmark:indexer', $command->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('limit', $command->getDefinition()
            ->getOption('limit')->getName());
        $this->assertEquals('microtime', $command->getDefinition()
            ->getOption('microtime')->getName());
    }

}