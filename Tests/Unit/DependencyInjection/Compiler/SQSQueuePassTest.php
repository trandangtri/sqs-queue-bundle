<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TriTran\SqsQueueBundle\DependencyInjection\Compiler\SQSQueuePass;
use TriTran\SqsQueueBundle\Tests\app\Worker\BasicWorker;

/**
 * Class SQSQueuePass
 * @package TriTran\SqsQueueBundle\DependencyInjection\Compiler
 */
class SQSQueuePassTest extends TestCase
{
    /**
     * @return ContainerBuilder
     */
    protected function getContainer(): ContainerBuilder
    {
        return new ContainerBuilder();
    }

    /**
     * Make sure AWS SQS was loaded successfully
     */
    public function testAwsSqsLoadedFailure()
    {
        $container = $this->getContainer();
        $compiler = new SQSQueuePass();

        $this->expectException(\InvalidArgumentException::class);
        $compiler->process($container);
    }

    /**
     * Make sure the worker of queue should be a valid and callable service.
     */
    public function testProcessFailure()
    {
        $container = $this->getContainer();
        $container->setDefinition('aws.sqs', new Definition());
        $container->setParameter(
            'tritran.sqs_queue.queues',
            ['queue-name' => ['queue_url' => 'my-url', 'worker' => 'bad-worker']]
        );

        $compiler = new SQSQueuePass();
        $this->expectException(\InvalidArgumentException::class);
        $compiler->process($container);
    }

    /**
     * @return array
     */
    public function configurationProvider(): array
    {
        $container = $this->getContainer();
        $container->setDefinition('aws.sqs', new Definition());

        $basicWorker = BasicWorker::class;
        $basicWorkerAsService = 'tritran.sqs_queue.fixture.basic_worker';
        $container->setDefinition($basicWorkerAsService, new Definition($basicWorker));

        return [
            // Case #0: Load a worker with default attributes
            [
                $container,
                [
                    'basic-queue' => [
                        'queue_url' => 'basic-url',
                        'worker' => $basicWorker,
                        'attributes' => []
                    ]
                ],
                [
                    'basic-queue' => [
                        'basic-url',
                        new Definition($basicWorker),
                        [
                            'DelaySeconds' => 0,
                            'MaximumMessageSize' => 262144,
                            'MessageRetentionPeriod' => 345600,
                            'ReceiveMessageWaitTimeSeconds' => 0,
                            'VisibilityTimeout' => 30
                        ]
                    ]
                ]
            ],
            // Case #1: Load a worker as a callable class with some attributes
            [
                $container,
                [
                    'basic-queue' => [
                        'queue_url' => 'basic-url',
                        'worker' => $basicWorker,
                        'attributes' => [
                            'delay_seconds' => 1,
                            'maximum_message_size' => 1,
                            'message_retention_period' => 1,
                            'receive_message_wait_time_seconds' => 1,
                            'visibility_timeout' => 1
                        ]
                    ]
                ],
                [
                    'basic-queue' => [
                        'basic-url',
                        new Definition($basicWorker),
                        [
                            'DelaySeconds' => 1,
                            'MaximumMessageSize' => 1,
                            'MessageRetentionPeriod' => 1,
                            'ReceiveMessageWaitTimeSeconds' => 1,
                            'VisibilityTimeout' => 1
                        ]
                    ]
                ]
            ],
            // Case #2: Load a worker as a service
            [
                $container,
                [
                    'basic-queue' => [
                        'queue_url' => 'basic-url',
                        'worker' => $basicWorkerAsService,
                        'attributes' => [
                            'delay_seconds' => 2,
                            'maximum_message_size' => 2,
                            'message_retention_period' => 2,
                            'receive_message_wait_time_seconds' => 2,
                            'visibility_timeout' => 2
                        ]
                    ]
                ],
                [
                    'basic-queue' => [
                        'basic-url',
                        new Reference($basicWorkerAsService),
                        [
                            'DelaySeconds' => 2,
                            'MaximumMessageSize' => 2,
                            'MessageRetentionPeriod' => 2,
                            'ReceiveMessageWaitTimeSeconds' => 2,
                            'VisibilityTimeout' => 2
                        ]
                    ]
                ]
            ],
            // Case #2: Load multi queues at the same time
            [
                $container,
                [
                    'basic-queue-1' => ['queue_url' => 'basic-url-1', 'worker' => $basicWorker],
                    'basic-queue-2' => ['queue_url' => 'basic-url-2', 'worker' => $basicWorkerAsService]
                ],
                [
                    'basic-queue-1' => [
                        'basic-url-1',
                        new Definition($basicWorker),
                        [
                            'DelaySeconds' => 0,
                            'MaximumMessageSize' => 262144,
                            'MessageRetentionPeriod' => 345600,
                            'ReceiveMessageWaitTimeSeconds' => 0,
                            'VisibilityTimeout' => 30
                        ]
                    ],
                    'basic-queue-2' => [
                        'basic-url-2',
                        new Reference($basicWorkerAsService),
                        [
                            'DelaySeconds' => 0,
                            'MaximumMessageSize' => 262144,
                            'MessageRetentionPeriod' => 345600,
                            'ReceiveMessageWaitTimeSeconds' => 0,
                            'VisibilityTimeout' => 30
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Load configuration of Machine Engine
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @param array $expectedArgs
     *
     * @dataProvider configurationProvider
     */
    public function testProcess($container, $config, $expectedArgs)
    {
        $container->setParameter('tritran.sqs_queue.queues', $config);

        $compiler = new SQSQueuePass();
        $compiler->process($container);

        foreach ($config as $queueName => $queueOption) {
            $queueId = sprintf('tritran.sqs_queue.%s', $queueName);

            $this->assertTrue($container->hasDefinition($queueId));

            $definition = $container->getDefinition($queueId);
            $this->assertEquals([
                new Reference('tritran.sqs_queue.queue_factory'),
                'create'
            ], $definition->getFactory());

            $this->assertEquals(
                array_merge([$queueName], $expectedArgs[$queueName]),
                $definition->getArguments()
            );
        }
    }
}
