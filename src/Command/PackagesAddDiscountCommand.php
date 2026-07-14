<?php

namespace App\Command;

use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:packages:add-discount',
    description: 'Add a short description for your command',
)]
#[AsCronTask('0 20-23,0-3 * * *')]
class PackagesAddDiscountCommand extends Command
{
    public function __construct(private readonly PackageRepository $packageRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $affectedRows = $this->packageRepository->reducePrices();

        $io->success(sprintf('Succes! %d packages have been updated', $affectedRows));

        return Command::SUCCESS;
    }

}
