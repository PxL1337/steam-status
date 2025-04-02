<?php

namespace App\Service;

class DatacenterRegions
{
    // Retourne "Europe", "Amérique", "Asie", "Océanie", "Afrique" ou "Autres" selon le nom
    public static function getRegionGroup(string $dcName): string
    {
        $dcName = mb_strtolower($dcName);

        // Vérifie explicitement "australia" avant de tester "us" ou "atlanta"
        if (str_contains($dcName, 'australia')) {
            return 'Oceania';
        }

        if (
            str_contains($dcName, 'eu') ||
            str_contains($dcName, 'europe') ||
            str_contains($dcName, 'uk') ||
            str_contains($dcName, 'germany') ||
            str_contains($dcName, 'finland') ||
            str_contains($dcName, 'spain') ||
            str_contains($dcName, 'sweden') ||
            str_contains($dcName, 'poland') ||
            str_contains($dcName, 'austria') ||
            str_contains($dcName, 'kingdom')
        ) {
            return 'Europe';
        }

        if (
            str_contains($dcName, 'us') ||
            str_contains($dcName, 'chicago') ||
            str_contains($dcName, 'virginia') ||
            str_contains($dcName, 'california') ||
            str_contains($dcName, 'atlanta') ||
            str_contains($dcName, 'south america') ||
            str_contains($dcName, 'argentina') ||
            str_contains($dcName, 'brazil') ||
            str_contains($dcName, 'chile') ||
            str_contains($dcName, 'peru')
        ) {
            return 'America';
        }

        if (
            str_contains($dcName, 'china') ||
            str_contains($dcName, 'japan') ||
            str_contains($dcName, 'singapore') ||
            str_contains($dcName, 'korea') ||
            str_contains($dcName, 'hong kong') ||
            str_contains($dcName, 'india')
        ) {
            return 'Asia';
        }

        if (str_contains($dcName, 'south africa')) {
            return 'Africa';
        }

        return 'Others';
    }
}
