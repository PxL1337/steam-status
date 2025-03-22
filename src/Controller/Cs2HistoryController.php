<?php

namespace App\Controller;

use App\Repository\Cs2StatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class Cs2HistoryController extends AbstractController
{
    #[Route('/cs2/history', name: 'cs2_history')]
    public function history(
        Cs2StatusRepository $repo,
        ChartBuilderInterface $chartBuilder
    ): Response {
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

        return $this->render('csgo/history.html.twig', [
            'chart' => $chart,
        ]);
    }
}
