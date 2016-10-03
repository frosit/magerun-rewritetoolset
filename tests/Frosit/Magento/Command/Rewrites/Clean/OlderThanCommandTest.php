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

/**
 * Class OlderThanCommandTest
 * @package Frosit\Magento\Command\Rewrites\Clean
 */
class OlderThanCommandTest extends TestCase
{

    public function setUp()
    {
        $application = $this->getApplication();
        $command = new OlderThanCommand();

        $application->add($command);
    }

    public function getCommand()
    {
        return $this->getApplication()
            ->find('rewrites:clean:older-than');
    }

    public function testName()
    {
        $command = $this->getCommand();
        $this->assertEquals('rewrites:clean:older-than', $command->getName());
    }

}