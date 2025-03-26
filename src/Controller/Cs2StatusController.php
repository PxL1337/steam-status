<?php

namespace App\Controller;

use App\Repository\Cs2StatusRepository;
use App\Service\DatacenterRegions;
use App\Service\DCMapper;
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
        // ex. $data['datacenters'] => "EU Germany"=>["capacity"=>..., "load"=>...]

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
                $region = DatacenterRegions::getRegionGroup($dcName);

                $mapped = DCMapper::mapCsgoDatacenter($dcName);
                // => [ 'city' => 'Frankfurt', 'country' => 'Germany', 'flag'=>'de' ]

                $capacity = $dcInfo['capacity'] ?? null;
                $load     = $dcInfo['load']     ?? null;

                // On range dans $groupedDatacenters[$region]
                if (!isset($groupedDatacenters[$region])) {
                    $region = 'Autres';
                }

                $groupedDatacenters[$region][] = [
                    'city' => $mapped['city'],       // ex. "Frankfurt"
                    'country' => $mapped['country'], // ex. "Germany"
                    'flag' => $mapped['flag'],       // ex. "de"
                    'capacity' => $capacity,
                    'load' => $load,
                ];
            }
        }

        // === Graph matchmaking (comme avant)
        $since = new \DateTime('-24 hours');
        $statuses = $repo->createQueryBuilder('c')
            ->where('c.fetchedAt > :since')
            ->setParameter('since', $since)
            ->orderBy('c.fetchedAt', 'ASC')
            ->getQuery()
            ->getResult();

        $labels = [];
        $playersData = [];
        $gameServers = [];
        $searchingPlayersData = [];
        $searchAverageData = [];
        foreach ($statuses as $status) {
            $labels[] = $status->getFetchedAt()->format('H:i');
            $rawData = $status->getRawData();
            $onlinePlayers = $rawData['matchmaking']['online_players'] ?? 0;
            $onlineServers = $rawData['matchmaking']['online_servers'] ?? 0;
            $searchingPlayers = $rawData['matchmaking']['searching_players'] ?? 0;
            $searchAverage = $rawData['matchmaking']['search_seconds_avg'] ?? 0;
            $playersData[] = $onlinePlayers;
            $gameServers[] = $onlineServers;
            $searchingPlayersData[] = $searchingPlayers;
            $searchAverageData[] = $searchAverage;
        }

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
                [
                    'label' => 'Online Game Servers (24h)',
                    'data' => $gameServers,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                ],
                [
                    'label' => 'Searching Players (24h)',
                    'data' => $searchingPlayersData,
                    'borderColor' => 'rgb(120, 200, 120)',
                    'backgroundColor' => 'rgb(120, 200, 120, 0.1)',
                ],
                [
                    'label' => 'Searching Average (24h)',
                    'data' => $searchAverageData,
                    'borderColor' => 'rgb(200, 200, 120)',
                    'backgroundColor' => 'rgb(200, 200, 120, 0.1)',
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => false],
            ],
        ]);

        // 4) On passe tout ça au template
        return $this->render('csgo/index.html.twig', [
            'csgo'               => $lastStatus,
            'data'               => $data,
            'chart'              => $chart,
            'groupedDatacenters' => $groupedDatacenters, // groupé par region
        ]);
    }
}
