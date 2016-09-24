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

namespace Frosit\Magento\Command\Rewrites\Clean;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DisabledCommandTest
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
class DisabledCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new DisabledCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:clean:disabled');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:clean:disabled', $command->getName());
    }

}