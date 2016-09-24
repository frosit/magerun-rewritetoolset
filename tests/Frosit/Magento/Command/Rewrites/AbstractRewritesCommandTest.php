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
namespace Frosit\Magento\Command\Rewrites;
use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AbstractRewriteCommandsTest
 */
class AbstractRewritesCommandsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractRewriteCommandsTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractRewritesCommandsTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new \Frosit\Magento\Command\Rewrites\Analysis\TotalsCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('store', $command->getDefinition()
            ->getOption('store')->getName());
    }
}