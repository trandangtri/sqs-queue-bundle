<?php

namespace TriTran\SqsQueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TriTran\SqsQueueBundle\Service\BaseQueue;

/**
 * Class SQSQueuePass
 * @package TriTran\SqsQueueBundle\DependencyInjection\Compiler
 */
class SQSQueuePass implements CompilerPassInterface
{
    /**
     * Load configuration of Machine Engine
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('aws.sqs')) {
            throw new \InvalidArgumentException(
                'AWS SQSClient is required to use queue.'
            );
        }

        if ($container->hasParameter('tritran.sqs_queue.queues')) {
            /** @var array $queues */
            $queues = $container->getParameter('tritran.sqs_queue.queues');
            foreach ($queues as $queueName => $queueOption) {
                $queueOption['worker'] = preg_replace('/^@/', '', $queueOption['worker']);
                if ($container->has($queueOption['worker'])) {
                    $callable = new Reference($queueOption['worker']);
                } elseif (class_exists($queueOption['worker'])) {
                    $callable = new Definition($queueOption['worker']);
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Invalid worker of queue [%s]', $queueName)
                    );
                }

                $queueDefinition = new Definition(BaseQueue::class);
                $queueDefinition
                    ->setFactory(
                        [
                            new Reference('tritran.sqs_queue.queue_factory'),
                            'create'
                        ]
                    )->setArguments([
                        $queueName,
                        $queueOption['queue_url'],
                        $callable,
                        [
                            'DelaySeconds' =>
                                $queueOption['attributes']['delay_seconds'] ?? 0,
                            'MaximumMessageSize' =>
                                $queueOption['attributes']['maximum_message_size'] ?? 262144,
                            'MessageRetentionPeriod' =>
                                $queueOption['attributes']['message_retention_period'] ?? 345600,
                            'ReceiveMessageWaitTimeSeconds' =>
                                $queueOption['attributes']['receive_message_wait_time_seconds'] ?? 0,
                            'VisibilityTimeout' =>
                                $queueOption['attributes']['visibility_timeout'] ?? 30
                        ]
                    ]);

                $queueId = sprintf('tritran.sqs_queue.%s', $queueName);
                $container->setDefinition($queueId, $queueDefinition);
            }
        }
    }
}
