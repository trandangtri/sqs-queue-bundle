<?php

namespace TriTran\SqsQueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('tritran_sqs_queue');

        $rootNode
            ->children()
                ->append($this->getSQSQueueNodeDef())
            ->end();

        return $tree;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function queuesNodeDef()
    {
        $node = new ArrayNodeDefinition('queues');

        return $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->append((new ScalarNodeDefinition('queue_url'))->isRequired())
                    ->append((new ScalarNodeDefinition('worker'))->isRequired())
                    ->append($this->getSQSQueueAttributesNodeDef())
                ->end()
            ->end();
    }

    /**
     * @return NodeDefinition|ParentNodeDefinitionInterface
     */
    protected function getSQSQueueAttributesNodeDef()
    {
        $node = new ArrayNodeDefinition('attributes');

        return $node
            ->children()
                ->append((new IntegerNodeDefinition('delay_seconds'))->defaultValue(0)->min(0)->max(900))// zero second
                ->append((new IntegerNodeDefinition('maximum_message_size'))->defaultValue(262144)->min(1024)->max(262144))// 256 KiB
                ->append((new IntegerNodeDefinition('message_retention_period'))->defaultValue(345600)->min(60)->max(1209600))// 4 days
                ->append((new IntegerNodeDefinition('receive_message_wait_time_seconds'))->defaultValue(0)->min(0)->max(20))// seconds
                ->append((new IntegerNodeDefinition('visibility_timeout'))->defaultValue(30)->min(0)->max(43200))// second
            ->end();
    }

    /**
     * @return NodeDefinition|ParentNodeDefinitionInterface
     */
    protected function getSQSQueueNodeDef()
    {
        $node = new ArrayNodeDefinition('sqs_queue');

        return $node
            ->canBeUnset()
            ->children()
                ->append($this->queuesNodeDef())
            ->end();
    }
}
