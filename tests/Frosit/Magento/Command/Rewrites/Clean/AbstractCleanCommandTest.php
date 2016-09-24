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
 * Class AbstractCleanCommandTest
 */
class AbstractCleanCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractCleanCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractCleanCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new DisabledCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('dry-run', $command->getDefinition()
            ->getOption('dry-run')->getName());
    }
}