<?php

namespace App\Service;

class DCMapper
{
    /**
     * Dictionnaire pour ICSGOServers_730 (ex: "EU Germany" => pays,flag).
     */
    private static array $csgoMap = [
        'eu_germany' => [
            'city' => 'Frankfurt',      // ex. "Frankfurt" si vous voulez un détail
            'country' => 'Germany',
            'flag' => 'de'
        ],
        'eu_spain' => [
            'city' => 'Madrid',
            'country' => 'Spain',
            'flag' => 'es'
        ],
        'eu_austria' => [
            'city' => 'Vienna',
            'country' => 'Austria',
            'flag' => 'at'
        ],
        'eu_poland' => [
            'city' => 'Warsaw',
            'country' => 'Poland',
            'flag' => 'pl'
        ],
        'eu_sweden' => [
            'city' => 'Stockholm',
            'country' => 'Sweden',
            'flag' => 'se'
        ],
        'eu_finland' => [
            'city' => 'Helsinki',
            'country' => 'Finland',
            'flag' => 'fi'
        ],
        'united_kingdom' => [
            'city' => 'London',
            'country' => 'United Kingdom',
            'flag' => 'gb'
        ],
        'us_atlanta' => [
            'city' => 'Atlanta',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'us_california' => [
            'city' => 'Los Angeles',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'us_seattle' => [
            'city' => 'Seattle',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'us_virginia' => [
            'city' => 'Virginia',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'us_chicago' => [
            'city' => 'Chicago',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'us_dallas' => [
            'city' => 'Dallas',
            'country' => 'United States',
            'flag' => 'us'
        ],
        'brazil' => [
            'city' => 'Sao Paulo',  // au besoin
            'country' => 'Brazil',
            'flag' => 'br'
        ],
        'argentina' => [
            'city' => 'Buenos Aires',  // ex
            'country' => 'Argentina',
            'flag' => 'ar'
        ],
        'australia' => [
            'city' => 'Sydney',
            'country' => 'Australia',
            'flag' => 'au'
        ],
        'hong_kong' => [
            'city' => 'Hong Kong',
            'country' => 'Hong Kong',  // ou 'Hong Kong (SAR)' selon votre choix
            'flag' => 'hk'
        ],
        'china_guangdong' => [
            'city' => 'Guangdong',
            'country' => 'China',
            'flag' => 'cn'
        ],
        'peru' => [
            'city' => 'Lima',
            'country' => 'Peru',
            'flag' => 'pe'
        ],
        'chile' => [
            'city' => 'Santiago',
            'country' => 'Chile',
            'flag' => 'cl'
        ],
        'japan' => [
            'city' => 'Tokyo',
            'country' => 'Japan',
            'flag' => 'jp'
        ],
        'china_pudong' => [
            'city' => 'Pudong',
            'country' => 'China',
            'flag' => 'cn'
        ],
        'india_mumbai' => [
            'city' => 'Mumbai',
            'country' => 'India',
            'flag' => 'in'
        ],
        'india_chennai' => [
            'city' => 'Chennai',
            'country' => 'India',
            'flag' => 'in'
        ],
        'south_korea' => [
            'city' => 'Seoul',
            'country' => 'South Korea',
            'flag' => 'kr'
        ],
        'singapore' => [
            'city' => 'Singapore',
            'country' => 'Singapore',
            'flag' => 'sg'
        ],
        'china_beijing' => [
            'city' => 'Beijing',
            'country' => 'China',
            'flag' => 'cn'
        ],
        'china_chengdu' => [
            'city' => 'Chengdu',
            'country' => 'China',
            'flag' => 'cn'
        ],
        'south_africa' => [
            'city' => 'Johannesburg',
            'country' => 'South Africa',
            'flag' => 'za'
        ],
        'emirates' => [
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'flag' => 'ae'
        ],
    ];


    /**
     * mapCsgoDatacenter($dcName): renvoie un tableau
     *  [ 'country'=>'Germany','flag'=>'de' ]
     */
    public static function mapCsgoDatacenter(string $dcName): array
    {
        $key = strtolower(trim($dcName));
        $key = str_replace(' ', '_', $key); // "EU Germany" => "eu_germany"

        if (isset(self::$csgoMap[$key])) {
            return [
                'city' => self::$csgoMap[$key]['city'],
                'country' => self::$csgoMap[$key]['country'],
                'flag' => self::$csgoMap[$key]['flag'],
            ];
        }
        // fallback
        return [
            'city' => null,
            'country' => $dcName,
            'flag' => null
        ];
    }


    /**
     * mapSteamDirectoryDc() (inchangé)
     */
    private static array $directoryMap = [
        'par1' => [
            'city' => 'Paris',
            'country' => 'France',
            'region' => 'Europe',
            'flag' => 'fr',
        ],
        // ...
    ];

    public static function mapSteamDirectoryDc(string $dc): array
    {
        $key = strtolower(trim($dc));
        return self::$directoryMap[$key] ?? [
            'city'    => $dc,
            'country' => 'Unknown',
            'region'  => 'Autres',
            'flag'    => null,
        ];
    }
}
