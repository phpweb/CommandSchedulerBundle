<?php

namespace Dukecity\CommandSchedulerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Dukecity\CommandSchedulerBundle\DependencyInjection\DukecityCommandSchedulerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DukecityCommandSchedulerBundle.
 */
class DukecityCommandSchedulerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';

        if (class_exists($ormCompilerClass))
        {
            $namespaces = ['Dukecity\CommandSchedulerBundle\Entity'];
            $directories = [realpath(__DIR__.'/Entity')];
            $managerParameters = [];
            $enabledParameter = false;
            $aliasMap = ['CommandSchedulerBundle' => 'Dukecity\CommandSchedulerBundle\Entity'];

            $driver = new Definition('Doctrine\ORM\Mapping\Driver\AttributeDriver', [$directories]);

            $container->addCompilerPass(
                new DoctrineOrmMappingsPass(
                    $driver,
                    $namespaces,
                    $managerParameters,
                    $enabledParameter,
                    $aliasMap
                )
            );

                # TODO
            /** If this is merged it could be renamed https://github.com/doctrine/DoctrineBundle/pull/1369/files
             * new DoctrineOrmMappingsPass(
             * DoctrineOrmMappingsPass::createPhpMappingDriver(
             * $namespaces,
            $directories,
            $managerParameters,
            $enabledParameter,
            $aliasMap)
             */
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return DukecityCommandSchedulerExtension
     */
    public function getContainerExtension(): DukecityCommandSchedulerExtension
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensionClass(): string
    {
        return DukecityCommandSchedulerExtension::class;
    }
}
