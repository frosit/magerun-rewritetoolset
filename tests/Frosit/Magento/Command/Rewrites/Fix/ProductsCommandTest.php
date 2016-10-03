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

use Frosit\Magento\Command\Rewrites\Fix\ProductsCommand;
use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ProductsCommandTest
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class ProductsCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new ProductsCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:fix:products');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:fix:products', $command->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('new-suffix', $command->getDefinition()
            ->getOption('new-suffix')->getName());
        $this->assertEquals('seperator', $command->getDefinition()
            ->getOption('seperator')->getName());
        $this->assertEquals('limit', $command->getDefinition()
            ->getOption('limit')->getName());
    }

}