<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AppException;
use App\Services\DiplomaService;
use ImagickException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DiplomaGenerateCommand extends Command
{
    public const OPTION_SCHOOL = 'school';
    public const OPTION_PERIOD = 'period';
    public const OPTION_LIMIT = 'limit';
    /**
     * @var string
     */
    protected static $defaultName = 'app:diploma:generate';

    public function __construct(public DiplomaService $service)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption(self::OPTION_SCHOOL, 's', InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_PERIOD, 'p', InputOption::VALUE_OPTIONAL)
            ->addOption(self::OPTION_LIMIT, 'l', InputOption::VALUE_OPTIONAL, 1);
    }

    /**
     * @return int|void|null
     *
     * @throws ImagickException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $limit = $input->hasOption(self::OPTION_LIMIT) ? (int) $input->getOption(self::OPTION_LIMIT) : 1;
            $idSchool = (int) $input->getOption(self::OPTION_SCHOOL);
            $school = $this->service->findSchool($idSchool);
            $period = $input->getOption(self::OPTION_PERIOD);

            if (null !== $period) {
                $period = $this->service->findPeriod($period);
            }

            $this->service->generate($school, $period, $limit);

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        } catch (AppException $e) {
            $io->error($e->getMessage());
        }
    }
}
