<?php
namespace Tests\Environment;

/**
 * Dotenv Test
 *
 * @group  environment
 */
class DotenvTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Check that Dotenv is loaded by composer
     */
    public function testDotenvClassLoaded()
    {
        $this->assertTrue(class_exists('Dotenv'), 'Dotenv class is not loaded');
    }
}
