<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MovieRepository;
use App\Entity\Movie;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
    public function new()
    {

        $movie = new Movie();
        
        $form = $this->createFormBuilder($movie)
            ->add('title', TextType::class)
            ->add('releaseYear', IntegerType::class)
            ->add('duration', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Add Movie'])
            ->getForm();
        return $this->render('movies/new.html.twig', ['form' => $form->createView()]); 
    }
}
