<?php

namespace App\Controller;

use App\Repository\Cs2StatusRepository;
use App\Service\DatacenterRegions;
use App\Service\DCMapper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class Cs2StatusController extends AbstractController
{
    #[Route('/', name: 'cs2_status')]
    public function index(
        Cs2StatusRepository $repo,
        ChartBuilderInterface $chartBuilder
    ): Response {
        // 1) Récupérer le plus récent enregistrement ICSGOServers_730
        $lastStatus = $repo->findOneBy([], ['fetchedAt' => 'DESC']);
        if (!$lastStatus) {
            return new Response('Pas de data en base, exécutez app:update-steAM-status');
        }

        // 2) Données brutes ICSGOServers
        $data = $lastStatus->getRawData();

        // 3) Construire un array groupé par région => [ "Europe"=> [ {...}, {...} ], "Amérique"=>[], ... ]
        $groupedDatacenters = [
            'Europe'   => [],
            'Amérique' => [],
            'Asie'     => [],
            'Océanie'  => [],
            'Afrique'  => [],
            'Autres'   => [],
        ];

        if (isset($data['datacenters']) && is_array($data['datacenters'])) {
            foreach ($data['datacenters'] as $dcName => $dcInfo) {
                // Déterminer la "region" via DatacenterRegions
                $region = DatacenterRegions::getRegionGroup($dcName);

                // Mapper "EU Germany" => city/country/flag
                $mapped = DCMapper::mapCsgoDatacenter($dcName);

                $capacity = $dcInfo['capacity'] ?? null;
                $load     = $dcInfo['load']     ?? null;

                if (!array_key_exists($region, $groupedDatacenters)) {
                    $region = 'Autres';
                }

                $groupedDatacenters[$region][] = [
                    'city' => $mapped['city'],
                    'country' => $mapped['country'],
                    'flag' => $mapped['flag'],
                    'capacity' => $capacity,
                    'load' => $load,
                ];
            }
        }

        // === Partie "Matchmaking" : créer 4 graphiques distincts sur 24h
        $since = new DateTime('-24 hours');
        $statuses = $repo->createQueryBuilder('c')
            ->where('c.fetchedAt > :since')
            ->setParameter('since', $since)
            ->orderBy('c.fetchedAt', 'ASC')
            ->getQuery()
            ->getResult();

        // On prépare 4 tableaux: OnlineServers, OnlinePlayers, SearchingPlayers, SearchAverage
        $labels = [];
        $onlineServersData = [];
        $onlinePlayersData = [];
        $searchingPlayersData = [];
        $searchAverageData = [];

        foreach ($statuses as $status) {
            $labels[] = $status->getFetchedAt()->format('H:i');
            $rawData  = $status->getRawData();
            $mm       = $rawData['matchmaking'] ?? [];

            $onlineServersData[]    = $mm['online_servers']      ?? 0;
            $onlinePlayersData[]    = $mm['online_players']      ?? 0;
            $searchingPlayersData[] = $mm['searching_players']   ?? 0;
            $searchAverageData[]    = $mm['search_seconds_avg']  ?? 0;
        }

        // 4) Construire 4 charts distincts
        $chartServers = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartServers->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Online Servers (24h)',
                    'data' => $onlineServersData,
                    'pointStyle' => 'line',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                ]
            ],
        ]);
        $chartServers->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false], // masque la légende
            ],
        ]);

        $chartPlayers = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartPlayers->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Online Players (24h)',
                    'data' => $onlinePlayersData,
                    'pointStyle' => 'line',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                ]
            ],
        ]);
        $chartPlayers->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false], // masque la légende
            ],
        ]);

        $chartSearching = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartSearching->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Searching Players (24h)',
                    'data' => $searchingPlayersData,
                    'pointStyle' => 'line',
                    'borderColor' => 'rgb(120, 200, 120)',
                    'backgroundColor' => 'rgba(120, 200, 120, 0.1)',
                ]
            ],
        ]);
        $chartSearching->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false], // masque la légende
            ],
        ]);

        $chartSearchAvg = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartSearchAvg->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Search Average (24h)',
                    'data' => $searchAverageData,
                    'pointStyle' => 'line',
                    'borderColor' => 'rgb(200, 200, 120)',
                    'backgroundColor' => 'rgba(200, 200, 120, 0.1)',
                ]
            ],
        ]);
        $chartSearchAvg->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false], // masque la légende
            ],
        ]);

        // On transmet tout ça au template
        return $this->render('csgo/index.html.twig', [
            'csgo' => $lastStatus,
            'data' => $data,
            // tableaux groupés par region (pour l'affichage datacenters)
            'groupedDatacenters' => $groupedDatacenters,
            // 4 mini-graph
            'chartServers'   => $chartServers,
            'chartPlayers'   => $chartPlayers,
            'chartSearching' => $chartSearching,
            'chartSearchAvg' => $chartSearchAvg,
        ]);
    }
}