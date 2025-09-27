<?php

// src/Controller/AccountHeaderController.php
namespace App\Controller;

use App\Entity\SteamUser;
use App\Message\UpdateSteamUserMessage;
use App\Service\SteamApi;
use App\Service\SteamUserUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use xPaw\Steam\SteamID;

final class AccountHeaderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SteamApi $steamApi,
        private readonly SteamUserUpdater $updater,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('/account', name: 'account_header')]
    public function __invoke(Request $req): Response
    {
        $q = trim((string)$req->query->get('q', ''));
        $user = null; $error = null; $updateQueued = false;

        if ($q !== '') {
            try {
                // Parse -> steam64 (supporte vanity/URL grâce au resolver)
                $sid = SteamID::SetFromURL($q, fn(string $v) => $this->steamApi->resolveVanity($v));
                $sid->SetAccountInstance(SteamID::DesktopInstance)->SetAccountUniverse(SteamID::UniversePublic);
                $steam64 = (string)$sid->ConvertToUInt64();

                // 1) Lire en DB si existe → afficher immédiatement
                $user = $this->em->getRepository(SteamUser::class)->find($steam64);

                if ($user) {
                    // 2) Essayer d’enfiler une mise à jour asynchrone (sans planter si file KO)
                    try {
                        $this->bus->dispatch(new UpdateSteamUserMessage($steam64));
                        $updateQueued = true;
                    } catch (\Throwable $e) {
                        // log seulement
                        $this->addFlash('warning', 'MAJ différée non disponible pour le moment.');
                    }
                } else {
                    // 3) Pas en DB → faire la MAJ synchronement (avec attente rate-limit)
                    $user = $this->updater->updateFromInput($steam64);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('calculator/header.html.twig', [
            'q' => $q,
            'user' => $user,
            'error' => $error,
            'updateQueued' => $updateQueued,
        ]);
    }
}
