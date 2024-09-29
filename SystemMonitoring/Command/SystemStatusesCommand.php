<?php

namespace Draw\Component\Application\SystemMonitoring\Command;

use Draw\Component\Application\SystemMonitoring\Status;
use Draw\Component\Application\SystemMonitoring\System;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SystemStatusesCommand extends Command
{
    public function __construct(private System $system)
    {
        parent::__construct('draw:system-monitoring:statuses');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Validate current system statuses.')
            ->addArgument(
                'context',
                InputArgument::OPTIONAL,
                'The context to use to validate the system statuses.',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($context = $input->getArgument('context')) {
            $monitoringResult = $this->system->getServiceStatuses($context);
        } else {
            $monitoringResult = $this->system->getAllServiceStatuses();
        }

        $io->title('System Statuses');

        foreach ($monitoringResult->getServiceStatuses() as $name => $serviceStatuses) {
            $io->section('Service '.$name);

            foreach ($serviceStatuses as $serviceStatus) {
                $subService = $serviceStatus->getName();

                switch ($serviceStatus->getStatus()) {
                    case Status::OK:
                        $io->success($subService);
                        break;
                    case Status::ERROR:
                        $io->error($subService);
                        break;
                    case Status::UNKNOWN:
                        $io->block($subService, 'UNKNOWN', 'fg=black;bg=yellow', ' ', true);
                        break;
                }
            }
        }

        $io->section('Results');

        switch ($monitoringResult->getStatus()) {
            default:
            case Status::OK:
                $io->success('All services are OK!');

                return Command::SUCCESS;
            case Status::UNKNOWN:
                $io->block('Some services are did not provide information!', 'UNKNOWN', 'fg=black;bg=yellow', ' ', true);

                return Command::SUCCESS;
            case Status::ERROR:
                $io->error('Service statuses issues.');

                return Command::FAILURE;
        }
    }
}
