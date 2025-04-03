<?php

namespace App\Controller;

use App\Repository\Cs2StatusRepository;
use App\Service\DatacenterRegions;
use App\Service\DCMapper;
use DateTime;
use IntlDateFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class Cs2StatusController extends AbstractController
{
    #[Route('/', name: 'cs2_status')]
    public function index(
        Cs2StatusRepository $repo,
        ChartBuilderInterface $chartBuilder,
        TranslatorInterface $translator,
        Request $request
    ): Response {
        $locale = $request->getLocale(); // récupère la locale Symfony (auto-déduite ou forcée)
        $dateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::MEDIUM,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            'EEE d MMM y HH\'h\'mm\'' // ex: Ven 27 mars 2025 19h34m05s
        );

        // 1) Récupérer le plus récent enregistrement ICSGOServers_730
        $lastStatus = $repo->findOneBy([], ['fetchedAt' => 'DESC']);
        $formattedFetchedAt = $dateFormatter->format($lastStatus->getFetchedAt());
        if (!$lastStatus) {
            return new Response('Pas de data en base, exécutez app:update-steAM-status');
        }

        // 2) Données brutes ICSGOServers
        $data = $lastStatus->getRawData();

        // 3) Construire un array groupé par région => [ "Europe"=> [ {...}, {...} ], "Amérique"=>[], ... ]
        $groupedDatacenters = [
            'Europe'   => [],
            'America' => [],
            'Asia'     => [],
            'Oceania'  => [],
            'Africa'  => [],
            'Emirates' => [],
            'Others'   => [],
        ];

        $translatedRegions = [];
        foreach (array_keys($groupedDatacenters) as $region) {
            $translatedRegions[$region] = $translator->trans('region.' . $region);
        }

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
                    'subgroup' => $mapped['subgroup'],
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

        // --- chartServers ---
        $chartServers = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartServers->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Online Servers (24h)',
                    'data' => $onlineServersData,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,                    // lissage
                    'cubicInterpolationMode' => 'monotone', // interpolation monotone
                ]
            ],
        ]);
        $chartServers->setOptions([
            'scales' => [
                'x' => [

                    'time' => [
                        'unit' => 'minute',         // ou 'hour', 'day'
                        'displayFormats' => [
                            'minute' => 'HH:mm',    // format d’affichage
                        ],
                    ],
                ],
                'y' => [
                    'beginAtZero' => false
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'elements' => [
                'point' => ['radius' => 0],
            ],
        ]);

        // --- chartPlayers ---
        $chartPlayers = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartPlayers->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Online Players (24h)',
                    'data' => $onlinePlayersData,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                ]
            ],
        ]);
        $chartPlayers->setOptions([
            'scales' => [
                'x' => [
                    'time' => [
                        'unit' => 'hour',         // ou 'hour', 'day'
                        'displayFormats' => [
                            'hour' => 'HH:mm',    // format d’affichage
                        ],
                    ],
                ],
                'y' => [
                    'beginAtZero' => false
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'elements' => [
                'point' => ['radius' => 0],
            ],
        ]);

        // --- chartSearching ---
        $chartSearching = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartSearching->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $translator->trans('searching_players') . ' (24h)',
                    'data' => $searchingPlayersData,
                    'borderColor' => 'rgb(120, 200, 120)',
                    'backgroundColor' => 'rgba(120, 200, 120, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                ]
            ],
        ]);
        $chartSearching->setOptions([
            'scales' => [
                'x' => [
                    'time' => [
                        'unit' => 'hour',         // ou 'hour', 'day'
                        'displayFormats' => [
                            'hour' => 'HH:mm',    // format d’affichage
                        ],
                    ],
                ],
                'y' => [
                    'beginAtZero' => false
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'elements' => [
                'point' => ['radius' => 0],
            ],
        ]);

        // --- chartSearchAvg ---
        $chartSearchAvg = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartSearchAvg->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $translator->trans('search_avg') . ' (24h)',
                    'data' => $searchAverageData,
                    'borderColor' => 'rgb(200, 200, 120)',
                    'backgroundColor' => 'rgba(200, 200, 120, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                ]
            ],
        ]);
        $chartSearchAvg->setOptions([
            'scales' => [
                'x' => [
                    'time' => [
                        'unit' => 'hour',         // ou 'hour', 'day'
                        'displayFormats' => [
                            'hour' => 'HH:mm',    // format d’affichage
                        ],
                    ],
                ],
                'y' => [
                    'beginAtZero' => false
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'elements' => [
                'point' => ['radius' => 0],
            ],
        ]);

        $versionMessages = [
            'version.message_1',
            'version.message_2',
            'version.message_3',
            'version.message_4',
            'version.message_5',
            'version.message_6',
            'version.message_7',
            'version.message_8',
            'version.message_9',
            'version.message_10',
            'version.message_11',
            'version.message_12',
            'version.message_13',
            'version.message_14',
            'version.message_15',
            'version.message_16',
            'version.message_17',
            'version.message_18',
            'version.message_19',
            'version.message_20',
        ];
        $versionChange = null;
        $currentVersion = $lastStatus->getAppVersion();
        $lastVersion = $currentVersion;

        $firstSeenNewVersion = null;
        $oldVersion = null;

        foreach ($statuses as $status) {
            $version = $status->getAppVersion();

            if ($version !== $lastVersion && $oldVersion === null) {
                // Changement détecté : on note l’ancienne version
                $oldVersion = $version;
            }

            if ($version === $currentVersion && $firstSeenNewVersion === null) {
                // Première fois qu’on voit la version actuelle dans la boucle (du plus ancien au plus récent)
                $firstSeenNewVersion = $dateFormatter->format($status->getFetchedAt());
            }

            $lastVersion = $version;
        }

        if ($oldVersion && $firstSeenNewVersion) {
            $messageKey = $versionMessages[array_rand($versionMessages)];

            $versionChange = [
                'old' => $oldVersion,
                'new' => $currentVersion,
                'date' => $firstSeenNewVersion,
                'message' => $translator->trans($messageKey),
            ];
        }

        $randomMessages = [
            'random.message_1',
            'random.message_2',
            'random.message_3',
            'random.message_4',
            'random.message_5',
            'random.message_6',
            'random.message_7',
            'random.message_8',
            'random.message_9',
            'random.message_10',
        ];
        $randomMessage = $translator->trans($randomMessages[array_rand($randomMessages)]);

        // On transmet tout ça au template
        return $this->render('csgo/index.html.twig', [
            'csgo' => $lastStatus,
            'data' => $data,
            // tableaux groupés par region (pour l'affichage datacenters)
            'groupedDatacenters' => $groupedDatacenters,
            'regionLabels' => $translatedRegions,
            // 4 mini-graph
            'chartServers'   => $chartServers,
            'chartPlayers'   => $chartPlayers,
            'chartSearching' => $chartSearching,
            'chartSearchAvg' => $chartSearchAvg,
            'versionChange' => $versionChange,
            'randomMessage' => $randomMessage,
            'formattedFetchedAt' => $formattedFetchedAt,
        ]);
    }
}