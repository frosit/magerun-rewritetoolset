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

namespace Frosit\Magento\Command\Rewrites\Benchmark;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AbstractBenchmarkCommandTest
 */
class AbstractBenchmarkCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractBenchmarkCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractBenchmarkCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new IndexerCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('save', $command->getDefinition()
            ->getOption('save')->getName());
    }
}