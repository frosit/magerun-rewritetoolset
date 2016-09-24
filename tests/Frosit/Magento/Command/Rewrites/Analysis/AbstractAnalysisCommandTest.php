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

namespace Frosit\Magento\Command\Rewrites\Analysis;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AbstractRewriteCommandTest
 */
class AbstractAnalysisCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractAnalysisCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractAnalysisCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }


    public function getCommand(){
        return new \Frosit\Magento\Command\Rewrites\Analysis\TotalsCommand();
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertEquals('save', $command->getDefinition()
            ->getOption('save')->getName());
    }
}