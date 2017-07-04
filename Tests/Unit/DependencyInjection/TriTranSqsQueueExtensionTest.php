<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TriTran\SqsQueueBundle\DependencyInjection\TriTranSqsQueueExtension;

/**
 * Class TriTranSqsQueueExtensionTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\DependencyInjection
 */
class TriTranSqsQueueExtensionTest extends TestCase
{
    /**
     * We customized the alias of Bundle, so we have to make sure it works as expected
     */
    public function testGetAlias()
    {
        $extension = new TriTranSqsQueueExtension();
        $this->assertEquals('tritran_sqs_queue', $extension->getAlias());
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainer(): ContainerBuilder
    {
        return new ContainerBuilder();
    }

    /**
     * Make sure the extension loaded all pre-defined services successfully
     */
    public function testPredefinedServicesLoaded()
    {
        $container = $this->getContainer();
        $extension = new TriTranSqsQueueExtension();
        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('tritran.sqs_queue.queue_factory'));
        $this->assertTrue($container->hasDefinition('tritran.sqs_queue.queue_worker'));
        $this->assertTrue($container->hasDefinition('tritran.sqs_queue.queue_manager'));
    }

    /**
     * Make sure the extension loaded all pre-defined parameters successfully via configuration
     */
    public function testPredefinedParametersLoaded()
    {
        $container = $this->getContainer();
        $extension = new TriTranSqsQueueExtension();
        $extension->load([
            'tritran_sqs_queue' => [
                'sqs_queue' => [
                    'queues' => [
                        ['name' => 'queue-1', 'queue_url' => 'url-1', 'worker' => 'worker-1'],
                        ['name' => 'queue-2', 'queue_url' => 'url-2', 'worker' => 'worker-2'],
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasParameter('tritran.sqs_queue.queues'));
    }
}
