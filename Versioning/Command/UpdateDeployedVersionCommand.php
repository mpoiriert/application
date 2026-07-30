<?php

namespace Draw\Component\Application\Versioning\Command;

use Draw\Component\Application\Versioning\VersionManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:application:update-deployed-version',
    description: 'You should run this after every successful application deployment.',
)]
class UpdateDeployedVersionCommand extends Command
{
    public function __construct(private VersionManager $versionManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->versionManager->updateDeployedVersion();

        (new SymfonyStyle($input, $output))
            ->success('Deployed Version set to: '.$this->versionManager->getRunningVersion())
        ;

        return 0;
    }
}
