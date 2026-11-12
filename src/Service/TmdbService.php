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

    public function searchMovies(string $title): array{
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
        return $data['results'] ?? [];
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
}
