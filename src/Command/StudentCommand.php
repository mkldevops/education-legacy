<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AppException;
use App\Manager\StudentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StudentCommand extends Command
{
    public const ARG_ACTION = 'action';
    public const OPT_LIMIT = 'limit';
    public const ACTION_SYNCHRONIZE = 'synchronize';

    /**
     * @var string
     */
    protected static $defaultName = 'app:education:student';

    /**
     * StudentCommand constructor.
     */
    public function __construct(private StudentManager $studentManager)
    {
        parent::__construct();
    }

    /**
     * configure.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('...')
            ->addArgument(self::ARG_ACTION, InputArgument::REQUIRED, 'Argument description')
            ->addOption(self::OPT_LIMIT, null, InputOption::VALUE_NONE, 'Option description');
    }

    /**
     * @throws AppException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $action = $input->getArgument(self::ARG_ACTION);
        $limit = $input->getOption(self::OPT_LIMIT);

        if (self::ACTION_SYNCHRONIZE === $action) {
            $result = $this->studentManager->synchronize($limit);
            $output->writeln('SUCCESS : '.json_encode($result->getResult()));
        } else {
            throw new AppException(sprintf('The action %s is not supported', $action));
        }
    }
}
