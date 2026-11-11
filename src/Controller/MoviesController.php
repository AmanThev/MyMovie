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
            throw $this->createNotFoundException('Film introuvable');
        }
        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies/new', name: 'app_movies_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response {
        $movie = new Movie();

        $form = $this->createForm(MovieType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('app_movies');
        }

        return $this->render('movies/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/movies/{id}/edit', name: 'app_movies_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, MovieRepository $movieRepository, Request $request, EntityManagerInterface $entityManager): Response {
        $movie = $movieRepository->find($id);
        if (!$movie) {
            throw $this->createNotFoundException('Movie not found');
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
}
