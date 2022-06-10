<?php

namespace Draw\Component\Application\Command;

use Draw\Component\Application\Cron\CronManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronDumpToFileCommand extends Command
{
    private CronManager $cronManager;

    public function __construct(CronManager $cronManager)
    {
        parent::__construct();
        $this->cronManager = $cronManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:cron:dump-to-file')
            ->setDescription('Dump the cron job configuration to a file compatible with crontab.')
            ->addArgument('filePath', InputArgument::REQUIRED, 'The file path where to dump.')
            ->addOption('override', null, InputOption::VALUE_NONE, 'If the file is present we override it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        if (is_file($filePath) && !$input->getOption('override')) {
            throw new RuntimeException(sprintf('The file [%s] already exists. Remove the file or use option --override.', $filePath));
        }

        file_put_contents($filePath, $this->cronManager->dumpJobs());

        return 0;
    }
}
