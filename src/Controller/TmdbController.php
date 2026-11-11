<?php

namespace App\Controller;

use App\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class TmdbController extends AbstractController
{
    #[Route('/tmdb/update/{id}', name: 'app_tmdb_update', methods: ['POST'])]
public function updateMovie(int $id, MovieRepository $movieRepository, TmdbService $tmdbService, EntityManagerInterface $entityManager): Response {
        $movie = $movieRepository->find($id);

        if (!$movie) {
            throw $this->createNotFoundException('Movie not found');
        }

        $data = $tmdbService->getMovieByTitle($movie->getTitle());
        if (empty($data)) {
            $this->addFlash('error', 'Movie not found on TMDB');

            return $this->redirectToRoute('app_movies_show', [
                'id' => $movie->getId()
            ]);
        }

        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);

        if ($data['releaseYear'] !== null) {
            $movie->setReleaseYear($data['releaseYear']);
        }

        if ($data['duration'] !== null) {
            $movie->setDuration($data['duration']);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Movie updated from TMDB');

        return $this->redirectToRoute('app_movies_show', [
            'id' => $movie->getId()
        ]);
    }
}