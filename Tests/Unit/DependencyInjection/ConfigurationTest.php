<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use TriTran\SqsQueueBundle\DependencyInjection\Configuration;

/**
 * Class ConfigurationTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\DependencyInjection
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test Configuration Definition
     */
    public function testGetConfigTreeBuilder()
    {
        $processor = new Processor();
        $processorConfig = $processor->processConfiguration(new Configuration(), []);
        $expectedConfiguration = [];
        $this->assertEquals($expectedConfiguration, $processorConfig);
    }
}
