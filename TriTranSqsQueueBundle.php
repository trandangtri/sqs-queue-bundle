<?php

namespace TriTran\SqsQueueBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TriTran\SqsQueueBundle\DependencyInjection\Compiler\SQSQueuePass;
use TriTran\SqsQueueBundle\DependencyInjection\TriTranSqsQueueExtension;

/**
 * Class TriTranSqsQueueBundle
 * @package TriTran\SqsQueueBundle
 */
class TriTranSqsQueueBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SQSQueuePass());
    }
    /**
     * @inheritdoc
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new TriTranSqsQueueExtension();
        }
        return $this->extension;
    }
}