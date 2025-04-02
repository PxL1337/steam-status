<?php

namespace App\Command;

use App\Service\SteamStatusService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


#[AsCommand(
    name: 'app:update-steam-status',
    description: 'Calls Steam API endpoints and stores them in DB'
)]
#[AsCronTask('* * * * *')]
class UpdateSteamStatusCommand extends Command
{
    private SteamStatusService $service;

    public function __construct(SteamStatusService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // On appelle nos deux méthodes
        $csgo = $this->service->updateCsgoStatus();
        $dir = $this->service->updateSteamDirectory();

        if (!$csgo && !$dir) {
            $output->writeln('<error>Failed to fetch data from Steam</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Steam data saved to DB</info>');
        return Command::SUCCESS;
    }
}
