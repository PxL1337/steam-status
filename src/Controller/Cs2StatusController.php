<?php

namespace App\Controller;

use App\Repository\Cs2StatusRepository;
use App\Service\DatacenterRegions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class Cs2StatusController extends AbstractController
{
    #[Route('/cs2', name: 'csgo_status')]
    public function index(Cs2StatusRepository $repo, ChartBuilderInterface $chartBuilder): Response
    {
        // Récupère le plus récent enregistrement
        $lastStatus = $repo->findOneBy([], ['fetchedAt' => 'DESC']);

        if (!$lastStatus) {
            return new Response('Pas de data en base, exécutez app:update-steam-status');
        }

        // Récupère les données brutes
        $data = $lastStatus->getRawData();

        // On prépare un tableau associatif pour regrouper par région
        $groupedDatacenters = [
            'Europe'    => [],
            'Amérique'  => [],
            'Asie'      => [],
            'Océanie'   => [],
            'Afrique'   => [],
            'Autres'    => [],
        ];

        // Vérifie si 'datacenters' est défini
        if (isset($data['datacenters']) && is_array($data['datacenters'])) {
            foreach ($data['datacenters'] as $dcName => $dcInfo) {
                // Détermine la région
                $region = DatacenterRegions::getRegionGroup($dcName);

                // Si la région n'existe pas, on met dans 'Autres'
                if (!array_key_exists($region, $groupedDatacenters)) {
                    $region = 'Autres';
                }

                $groupedDatacenters[$region][] = [
                    'name'     => $dcName,
                    'capacity' => $dcInfo['capacity'] ?? null,
                    'load'     => $dcInfo['load'] ?? null,
                ];
            }
        }

        // On récupère les 24 dernières heures
        $since = new \DateTime('-24 hours');
        $statuses = $repo->createQueryBuilder('c')
            ->where('c.fetchedAt > :since')
            ->setParameter('since', $since)
            ->orderBy('c.fetchedAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Préparation des labels (heure ou format) et data (online_players)
        $labels = [];
        $playersData = [];

        foreach ($statuses as $status) {
            $labels[] = $status->getFetchedAt()->format('H:i');
            $rawData = $status->getRawData();
            $onlinePlayers = $rawData['matchmaking']['online_players'] ?? 0;
            $playersData[] = $onlinePlayers;
        }

        // Construire le chart
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Online Players (24h)',
                    'data' => $playersData,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'beginAtZero' => false
                ]
            ],
        ]);

        return $this->render('csgo/index.html.twig', [
            'csgo'                => $lastStatus,
            'data'                => $data,
            'groupedDatacenters'  => $groupedDatacenters,
            'chart' => $chart,
        ]);
    }
}
