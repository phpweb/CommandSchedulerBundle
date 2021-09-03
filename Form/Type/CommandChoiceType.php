<?php

namespace Dukecity\CommandSchedulerBundle\Form\Type;

use Dukecity\CommandSchedulerBundle\Service\CommandParser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommandChoiceType.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class CommandChoiceType extends AbstractType
{
    /**
     * CommandChoiceType constructor.
     * @param CommandParser $commandParser
     */
    public function __construct(private CommandParser $commandParser)
    {
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => $this->commandParser->getCommands(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
