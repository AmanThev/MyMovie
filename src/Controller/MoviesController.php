<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MovieRepository;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\MovieType;
use App\Service\TmdbService;

final class MoviesController extends AbstractController
{
    #[Route('/movies', name: 'app_movies', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {
        $movies = $movieRepository->findAll();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies,
        ]);
    }

    #[Route('/movies/{id}', name: 'app_movies_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->find($id);
        if(!$movie){
            throw $this->createNotFoundException('Movie not found');
        }
        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies/new', name: 'app_movies_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TmdbService $tmdbService): Response {
        $movie = new Movie();
        $searchResults = [];
        $search = $request->query->get('search');
        if (is_string($search) && trim($search) !== '') {
            $searchResults = $tmdbService->searchMovies($search);
        }

        $tmdbId = $request->query->get('tmdb_id');
        if (is_string($tmdbId) && ctype_digit($tmdbId)) {
            $details = $tmdbService->getMovieDetails((int) $tmdbId);
            if (!empty($details['title'])) {
                $movie->setTitle($details['title']);
            }
            if (!empty($details['release_date'])) {
                $movie->setReleaseYear(
                    (int) substr($details['release_date'], 0, 4)
                );
            }
            if (isset($details['runtime'])) {
                $movie->setDuration($details['runtime']);
            }
            $movie->setDescription($details['overview'] ?? '');
            $movie->setPosterPath($details['poster_path'] ?? null);
        }

        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($movie);
            $entityManager->flush();
            $this->addFlash('success', 'Movie has been added');

            return $this->redirectToRoute('app_movies');
        }

        return $this->render('movies/new.html.twig', [
            'form' => $form->createView(),
            'searchResults' => $searchResults,
        ]);
    }

    
    #[Route('/movies/{id}/edit', name: 'app_movies_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, MovieRepository $movieRepository, Request $request, EntityManagerInterface $entityManager, TmdbService $tmdbService): Response {
        $movie = $movieRepository->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Movie not found');
        }

        $searchResults = [];
        $search = $request->query->get('search');
        if (is_string($search) && trim($search) !== '') {
            $searchResults = $tmdbService->searchMovies($search);
        }

        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Movie has been updated');
            return $this->redirectToRoute('app_movies_show', [
                'id' => $movie->getId()
            ]);
        }

        return $this->render('movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
            'searchResults' => $searchResults,
        ]);
    }


    #[Route('/movies/{id}/delete', name: 'app_movies_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete (int $id, MovieRepository $movieRepository, Request $request, EntityManagerInterface $entityManager): Response {
        $movie = $movieRepository->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Movie not found');
        }

        if ($this->isCsrfTokenValid(
            'delete' . $movie->getId(),
            $request->request->get('_token')
        )) {
            $entityManager->remove($movie);
            $entityManager->flush();
            $this->addFlash('success', 'Movie has been deleted');

            return $this->redirectToRoute('app_movies');
        }

        throw $this->createAccessDeniedException('Invalid CSRF token');
    }


    #[Route('/movies/{id}/poster', name: 'app_movies_poster', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updatePoster(int $id, MovieRepository $movieRepository, Request $request, TmdbService $tmdbService, EntityManagerInterface $entityManager): Response {
        $movie = $movieRepository->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Movie not found');
        }

        if (!$this->isCsrfTokenValid(
            'poster' . $movie->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $tmdbId = $request->request->get('tmdb_id');
        if (!is_string($tmdbId) || !ctype_digit($tmdbId)) {
            throw $this->createNotFoundException('Invalid TMDB ID');
        }

        $details = $tmdbService->getMovieDetails((int) $tmdbId);
        $posterPath = $details['poster_path'] ?? null;
        if (!$posterPath) {
            $this->addFlash('error', 'No poster available on TMDB');
            return $this->redirectToRoute('app_movies_edit', [
                'id' => $movie->getId()
            ]);
        }

        $movie->setPosterPath($posterPath);
        $entityManager->flush();
        $this->addFlash('success', 'Movie poster has been updated');

        return $this->redirectToRoute('app_movies_show', [
            'id' => $movie->getId()
        ]);
    }

}
