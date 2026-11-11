<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TmdbService
{
    public function __construct(
        private HttpClientInterface $httpClient,

        #[Autowire('%env(TMDB_API_TOKEN)%')]
        private string $apiToken
    ) {
    }


    public function searchMovie(string $title): array{
        $response = $this->httpClient->request(
            'GET',
            'https://api.themoviedb.org/3/search/movie',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json'
                ],
                'query' => [
                    'query' => $title,
                    'language' => 'en-US'
                ]
            ]
        );

        $data = $response->toArray();

        if (empty($data['results'])) {
            return [];
        }

        $movie = $data['results'][0];

        return [
            'title' => $movie['title'],
            'description' => $movie['overview'],
            'release_year' => !empty($movie['release_date'])
                ? (int) substr($movie['release_date'], 0, 4)
                : null,
            'tmdb_id' => $movie['id']
        ];
    }

    public function getMovieDetails(int $id): array{
        $response = $this->httpClient->request(
            'GET',
            'https://api.themoviedb.org/3/movie/' . $id,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json'
                ],
                'query' => [
                    'language' => 'en-US'
                ]
            ]
        );

        return $response->toArray();
    }

    public function getMovieByTitle(string $title): array{
        // Rechercher le film
        $movie = $this->searchMovie($title);

        if (empty($movie)) {
            return [];
        }

        // Récupérer les détails avec son identifiant TMDB
        $details = $this->getMovieDetails($movie['tmdb_id']);

        // Préparer les informations pour MyMovies
        return [
            'title' => $details['title'] ?? $movie['title'],
            'releaseYear' => !empty($details['release_date'])
                ? (int) substr($details['release_date'], 0, 4)
                : null,
            'duration' => $details['runtime'] ?? null,
            'description' => $details['overview'] ?? '',
            'posterPath' => $details['poster_path'] ?? null
        ];
    }
    
}