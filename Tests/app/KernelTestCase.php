<?php

namespace TriTran\SqsQueueBundle\Tests\app;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KernelTestCase
 * @package TriTran\SqsQueueBundle\Tests
 */
class KernelTestCase extends SymfonyKernelTestCase
{
    /**
     * @var ContainerInterface
     */
    private $cont;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->cont = $container;
    }

    /**
     * Returns service container.
     *
     * @param bool $reinitialize Force kernel reinitialization.
     * @param array $kernelOptions Options used passed to kernel if it needs to be initialized.
     *
     * @return ContainerInterface
     */
    protected function getContainer($reinitialize = false, array $kernelOptions = [])
    {
        if ($this->cont === null || $reinitialize) {
            static::bootKernel($kernelOptions);
            $this->cont = static::$kernel->getContainer();
        }

        return $this->cont;
    }

    /**
     * @inheritdoc
     */
    protected static function createKernel(array $options = [])
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

        return $kernel;
    }

    /**
     * @param ContainerAwareCommand $command
     *
     * @return CommandTester
     */
    public function createCommandTester(ContainerAwareCommand $command)
    {
        $application = new Application();
        $command->setContainer($this->getContainer());
        $application->add($command);

        return new CommandTester($application->find($command->getName()));
    }
}
