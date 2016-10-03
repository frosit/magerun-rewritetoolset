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
 * Class AbstractLogCommandTest
 * @package Frosit\Magento\Command\Rewrites\Log
 */
class AbstractUrlCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractUrlCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractUrlCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new CsvCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('csv', $command->getDefinition()
            ->getOption('csv')->getName());
    }
}