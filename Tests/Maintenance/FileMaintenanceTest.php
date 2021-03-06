<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Test driver file
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class FileDriverTest extends TestCase
{
    static protected $tmpDir;
    protected $container;

    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$tmpDir = sys_get_temp_dir().'/symfony2_finder';
    }

    public function setUp()
    {
        $this->container = $this->initContainer();
    }

    public function tearDown()
    {
        $this->container = null;
    }

    public function testDecide()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock');

        $fileM = new FileDriver($this->getTranslator(), $options);

        $this->assertTrue($fileM->decide());

        $options = array('file_path' => self::$tmpDir.'/clok');

        $fileM2 = new FileDriver($this->getTranslator(), $options);
        $this->assertFalse($fileM2->decide());
    }

    /**
     *
     * @expectedException InvalidArgumentException
     */
    public function testExceptionInvalidPath()
    {
        $fileM = new FileDriver($this->getTranslator(), array());
    }

    public function testLock()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock');

        $fileM = new FileDriver($this->getTranslator(), $options);
        $fileM->lock();

        $this->assertFileExists($options['file_path']);
    }

    public function testUnlock()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock');

        $fileM = new FileDriver($this->getTranslator(), $options);
        $fileM->lock();

        $fileM->unlock();

        $this->assertFileNotExists($options['file_path']);
    }

    public function testIsExists()
    {
        $options = array('file_path' => self::$tmpDir.'/lock.lock', 'ttl' => 3600);

        $fileM = new FileDriver($this->getTranslator(), $options);
        $fileM->lock();

        $this->assertTrue($fileM->isEndTime(3600));
    }

    static public function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    protected function initContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
                                        'kernel.debug'       => false,
                                        'kernel.bundles'     => array('MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'),
                                        'kernel.cache_dir'   => sys_get_temp_dir(),
                                        'kernel.environment' => 'dev',
                                        'kernel.root_dir'    => __DIR__.'/../../../../' // src dir
        )));

        return $container;
    }

    public function getTranslator()
    {
        $translator = new Translator(
            $this->container,
            $this->getMock('Symfony\Component\Translation\MessageSelector')
        );

        return $translator;
    }
}
