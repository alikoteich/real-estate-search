<?php

namespace App\Service;

class PropertyQueryParser
{
    private const PROPERTY_SYNONYMS = [
        'flat' => 'apartment',  // Map flat → apartment
        'condo' => 'apartment',
        'studio' => 'apartment',
        'villa' => 'house',
        'townhouse' => 'house'
    ];

    public function parse(string $query): array
    {
        $criteria = [
            'type' => null,
            'bedrooms' => null,
            'price' => null,
            'location' => null,
        ];

        // Extract property type (e.g., "apartment", "house")
        preg_match('/\b(apartment|flat|house|condo|villa|townhouse|studio)\b/i', $query, $typeMatches);
        if (!empty($typeMatches)) {
            $matchedType = strtolower($typeMatches[1]);
            $criteria['type'] = self::PROPERTY_SYNONYMS[$matchedType] ?? $matchedType;
        }

        // Extract bedrooms (e.g., "3-bedroom", "2 br")
        preg_match('/(\d+)\s*-?\s*(?:bedroom|bedrooms?)\b/i', $query, $bedMatches);
        if (!empty($bedMatches)) {
            $criteria['bedrooms'] = (int)$bedMatches[1];
        }

        // 1. Check for price ranges first (most specific)
        preg_match(
            '/\b(between|from)\s+(\$?)(\d+[kK]?)\s+and\s+(\$?)(\d+[kK]?)(?=\s|$)/i',
            $query,
            $rangeMatches
        );

        // 2. Check for operator-based prices (under/over)
        preg_match(
            '/(?:under|below|less than|more than|above|over|around)\s+(\$?)(\d+[kK]?)\b/i',
            $query,
            $priceMatches
        );

        // 3. Check for exact prices (standalone values)
        preg_match(
            '/(?:^|\s)(\$?\d+[kK])\b(?:$|\s)/i',
            $query,
            $exactPriceMatches
        );

        if (!empty($rangeMatches)) {
            // Handle price ranges (e.g., "between $200k and $500k")
            $min = str_ireplace(['$', 'k'], ['', '000'], $rangeMatches[2] . $rangeMatches[3]);
            $max = str_ireplace(['$', 'k'], ['', '000'], $rangeMatches[4] . $rangeMatches[5]);

            $criteria['price'] = [
                'operator' => 'BETWEEN',
                'min' => (int)$min,
                'max' => (int)$max,
            ];
        } elseif (!empty($priceMatches)) {
            // Handle operator-based prices (e.g., "under $500k")
            $value = str_ireplace(['$', 'k'], ['', '000'], $priceMatches[1] . $priceMatches[2]);
            $operator = match (true) {
                stripos($priceMatches[0], 'under') !== false => '<',
                stripos($priceMatches[0], 'below') !== false => '<',
                stripos($priceMatches[0], 'less than') !== false => '<',
                stripos($priceMatches[0], 'more than') !== false => '>',
                stripos($priceMatches[0], 'above') !== false => '>',
                stripos($priceMatches[0], 'over') !== false => '>',
                default => '='
            };
            $criteria['price'] = [
                'operator' => $operator,
                'value' => (int)$value
            ];
        } elseif (!empty($exactPriceMatches)) {
            // Handle exact prices (e.g., "$400k" or "500k")
            $value = str_ireplace(['$', 'k'], ['', '000'], $exactPriceMatches[0]);
            $criteria['price'] = [
                'operator' => '=',
                'value' => (int)$value
            ];
        }

        // Extract location (e.g., "in Paris", "located in New York")
        preg_match('/(?:in|at|located in)\s+((?:[A-Z][a-z]+\s?)+)/', $query, $locationMatches);
        if (!empty($locationMatches)) {
            $location = trim($locationMatches[1]);
            $criteria['location'] = ucwords(strtolower($location));
        }

        return $criteria;
    }
}
