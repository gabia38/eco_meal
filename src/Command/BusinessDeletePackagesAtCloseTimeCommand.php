<?php

namespace App\Command;

use App\Repository\BusinessRepository;
use App\Repository\PackageRepository;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use function Symfony\Component\Clock\now;

#[AsCommand(
    name: 'app:business:delete-packages-at-close-time',
    description: 'Add a short description for your command',
)]
#[AsCronTask('0/5,59 * * * *')]
class BusinessDeletePackagesAtCloseTimeCommand extends Command
{
    public function __construct(private  readonly BusinessRepository $businessRepository, private readonly PackageRepository $packageRepository)
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

        $time = now();
        $minute = (int) $time->format('i');

        if($minute===0) {
            $businesses = $this->businessRepository->findByClosingTimeInterval($time,$time);
        }
        else {
            $time5MinuteAgo = (clone $time)->modify('-5 minutes');
            $businesses = $this->businessRepository->findByClosingTimeInterval($time5MinuteAgo,$time);
        }

        $deletedPackages= $this->packageRepository->deletePackagesByBusinesses($businesses);
        $io->success(sprintf('Deleted %d packages', $deletedPackages));

        return Command::SUCCESS;
    }
}
