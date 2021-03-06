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
 * Class UrlResolveCommandTest
 * @package Frosit\Magento\Command\Rewrites\Benchmark
 */
class UrlResolveCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new UrlResolveCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:benchmark:resolve-urls');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:benchmark:resolve-urls', $command->getName());
    }

}