<?php

// src/MessageHandler/UpdateSteamUserMessageHandler.php
namespace App\MessageHandler;

use App\Message\UpdateSteamUserMessage;
use App\Service\SteamUserUpdater;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateSteamUserMessageHandler
{
    public function __construct(private readonly SteamUserUpdater $updater) {}

    public function __invoke(UpdateSteamUserMessage $msg): void
    {
        $this->updater->updateFromInput($msg->input);
    }
}

