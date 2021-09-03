<?php

namespace Dukecity\CommandSchedulerBundle\Controller;

use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface as ContractsTranslatorInterface;

/**
 * Class BaseController.
 *
 * @author Julien Guyon <julienguyon@hotmail.com>
 */
abstract class AbstractBaseController extends AbstractController
{
    private string $managerName;

    /**
     * @var ContractsTranslatorInterface
     */
    protected ContractsTranslatorInterface $translator;

    /**
     * @param string $managerName
     */
    public function setManagerName(string $managerName)
    {
        $this->managerName = $managerName;
    }

    public function getManagerName(): string
    {
        return $this->managerName;
    }

    /**
     * @param ContractsTranslatorInterface $translator
     */
    public function setTranslator(ContractsTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager($this->managerName);
    }
}
