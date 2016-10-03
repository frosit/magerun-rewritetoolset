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

namespace Frosit\Magento\Command\Rewrites\Log;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ParseCommandTest
 * @package Frosit\Magento\Command\Rewrites\Log
 */
class ParseCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new ParseCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:log:parse');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:log:parse', $command->getName());
    }
    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('file', $command->getDefinition()
            ->getOption('file')->getName());
        $this->assertEquals('to-db', $command->getDefinition()
            ->getOption('to-db')->getName());
        $this->assertEquals('clean', $command->getDefinition()
            ->getOption('clean')->getName());
        $this->assertEquals('webserver', $command->getDefinition()
            ->getOption('webserver')->getName());
        $this->assertEquals('platform', $command->getDefinition()
            ->getOption('platform')->getName());
    }

}