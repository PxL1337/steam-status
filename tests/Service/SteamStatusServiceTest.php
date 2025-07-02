<?php

namespace App\Tests\Service;

use App\Entity\Cs2Status;
use App\Service\SteamStatusService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SteamStatusServiceTest extends TestCase
{
    public function testUpdateCsgoStatusReturnsEntity()
    {
        $responseData = [
            'result' => [
                'app' => ['version' => 123, 'timestamp' => 456],
                'matchmaking' => ['online_players' => 100],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($responseData);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $this->stringContains('ICSGOServers_730'))
            ->willReturn($response);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Cs2Status::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $service = new SteamStatusService($httpClient, $entityManager, 'key');
        $status = $service->updateCsgoStatus();

        $this->assertInstanceOf(Cs2Status::class, $status);
        $this->assertSame(123, $status->getAppVersion());
        $this->assertSame(456, $status->getAppTimestamp());
        $this->assertSame($responseData['result'], $status->getRawData());
        $this->assertNotNull($status->getFetchedAt());
    }
}
