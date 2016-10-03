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

namespace Frosit\Magento\Command;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Frosit\Magento\Command\Rewrites\Analysis\TotalsCommand;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractCommandTest
     */
    protected $_abstract;

    public function setUp()
    {
        $abstractClass = $this->getMockForAbstractClass(AbstractCommandTest::class, ['testAbstract']);
        $this->_abstract = $abstractClass;
    }

    public function getCommand()
    {
        return new TotalsCommand();
    }

    public function testSetDefaultOptions()
    {
        $command = $this->getCommand();

        $this->assertEquals('log-statistics', $command->getDefinition()
            ->getOption('log-statistics')->getName());
        $this->assertEquals('share-statistics', $command->getDefinition()
            ->getOption('share-statistics')->getName());
    }

}