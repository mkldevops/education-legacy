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

class DiplomaGenerateCustomCommand extends Command
{
    public const OPTION_TEXT = 'text';
    public const OPTION_ID = 'id';

    protected static $defaultName = 'app:diploma:generate:custom';
    private DiplomaService $service;

    public function __construct(DiplomaService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption(self::OPTION_TEXT, 't', InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_ID, 'i', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @return int|void|null
     *
     * @throws ImagickException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $name = $input->getOption(self::OPTION_TEXT);
            $id = $input->getOption(self::OPTION_ID);

            $this->service->getDiplomaStudent($name, $id);

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        } catch (AppException $e) {
            $io->error($e->getMessage());
        }
    }
}
