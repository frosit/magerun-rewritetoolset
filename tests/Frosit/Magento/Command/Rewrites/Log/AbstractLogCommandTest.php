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
 * Class AbstractLogCommandTest
 * @package Frosit\Magento\Command\Rewrites\Log
 */
class AbstractLogCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractLogCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractLogCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new ParseCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('file', $command->getDefinition()
            ->getOption('file')->getName());
    }
}