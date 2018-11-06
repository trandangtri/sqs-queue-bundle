<?php

namespace TriTran\SqsQueueBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TriTranSqsQueueExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configSQSQueue($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configSQSQueue(ContainerBuilder $container, array $config)
    {
        $engineConfig = $config['sqs_queue'] ?? [];

        if (isset($engineConfig['queues']) && !empty($engineConfig['queues'])) {
            $container->setParameter('tritran.sqs_queue.queues', $config['sqs_queue']['queues']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'tritran_sqs_queue';
    }
}
