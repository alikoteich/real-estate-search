<?php

namespace App\DataFixtures;

use App\Entity\Property;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PropertyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $properties = [
            // Paris (Main City)
            ['type' => 'apartment', 'bedrooms' => 2, 'price' => 450000, 'location' => 'Paris'],
            ['type' => 'apartment', 'bedrooms' => 3, 'price' => 400000, 'location' => 'Paris'], // Better Deal (Lower Price)
            ['type' => 'apartment', 'bedrooms' => 3, 'price' => 550000, 'location' => 'Paris'], // Premium (Higher Price)
            ['type' => 'apartment', 'bedrooms' => 2, 'price' => 420000, 'location' => 'Lyon'],  // Nearby Location (Lyon)
            ['type' => 'house', 'bedrooms' => 4, 'price' => 480000, 'location' => 'Marseille'], // Nearby Location (Marseille)

            // New York (Main City)
            ['type' => 'apartment', 'bedrooms' => 1, 'price' => 380000, 'location' => 'New York'], // Better Deal
            ['type' => 'apartment', 'bedrooms' => 2, 'price' => 600000, 'location' => 'New York'], // Premium
            ['type' => 'condo', 'bedrooms' => 2, 'price' => 350000, 'location' => 'Boston'],       // Nearby Location (Boston)
            ['type' => 'house', 'bedrooms' => 3, 'price' => 520000, 'location' => 'Philadelphia'], // Nearby Location (Philadelphia)

            // Edge Case (High-End Property)
            ['type' => 'house', 'bedrooms' => 5, 'price' => 700000, 'location' => 'Paris'],
        ];

        foreach ($properties as $data) {
            $property = new Property();
            $property->setType($data['type']);
            $property->setBedrooms($data['bedrooms']);
            $property->setPrice($data['price']);
            $property->setLocation($data['location']);
            $manager->persist($property);
        }

        $manager->flush();
    }
}
