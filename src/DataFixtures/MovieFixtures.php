<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $movies = [
            ['Insterstellar', 2014, 169, 'Un voyage à travers l\'espace pour sauver l\'humanité.'],
            ['The Dark Knight', 2008, 152, 'Batman affronte le Joker dans une lutte pour Gotham'],
            ['Inception', 2010, 148, 'Un voleur s\'infiltre dans le subconscient des gens'],
            ['Avater', 2009, 162, 'Un ex-marine est envoyé sur la planète Pandora'],
            ['Gladiator', 2000, 155, 'Un général romain trahi chercher à se venger'],
        ];

        foreach ($movies as [$title, $year, $duration, $desc]){
            $movie = new Movie();
            $movie  ->setTitle($title)
                    ->setReleaseYear($year)
                    ->setDuration($duration)
                    ->setDescription($desc);
            $manager->persist($movie);
        }

        $manager->flush();
    }
}
