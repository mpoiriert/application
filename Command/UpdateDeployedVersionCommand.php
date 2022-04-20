<?php

namespace Draw\Component\Application\Command;

use Draw\Component\Application\VersionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateDeployedVersionCommand extends Command
{
    private VersionManager $versionManager;

    public function __construct(VersionManager $versionManager)
    {
        parent::__construct();
        $this->versionManager = $versionManager;
    }

    protected function configure()
    {
        $this
            ->setName('draw:application:update-deployed-version')
            ->setDescription('You should run this after every successful application deployment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->versionManager->updateDeployedVersion();

        (new SymfonyStyle($input, $output))
            ->success('Deployed Version set to: '.$this->versionManager->getRunningVersion());

        return 0;
    }
}
