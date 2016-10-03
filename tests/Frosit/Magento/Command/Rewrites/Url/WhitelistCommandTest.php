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

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class VisitorCommandTest
 * @package Frosit\Magento\Command\Rewrites\Url
 */
class WhitelistCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new WhitelistCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:url:whitelist');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:url:whitelist', $command->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('max-age', $command->getDefinition()
            ->getOption('max-age')->getName());
        $this->assertEquals('debug', $command->getDefinition()
            ->getOption('debug')->getName());
    }

}