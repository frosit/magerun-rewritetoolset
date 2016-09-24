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

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TotalsCommandTest
 * @package Frosit\Magento\Command\Rewrites\Analysis
 */
class TotalsCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new TotalsCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:analysis:totals');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:analysis:totals', $command->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('store', $command->getDefinition()
            ->getOption('store')->getName());
    }

}