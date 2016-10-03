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

namespace Frosit\Magento\Command\Rewrites\Fix;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AbstractFixCommandTest
 * @package Frosit\Magento\Command\Rewrites\Fix
 */
class AbstractFixCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractFixCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractFixCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new ProductsCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('dry-run', $command->getDefinition()
            ->getOption('dry-run')->getName());
    }
}