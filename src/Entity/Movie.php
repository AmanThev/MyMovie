<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'The movie title is required'
    )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'The title must contain at least 2 characters',
        maxMessage: 'The title cannot exceed 255 characters'
    )]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'This field is required.')]
    #[Assert\Range(
        min: 1900,
        maxPropertyPath: 'currentYear',
        notInRangeMessage: 'The release year must be between {{ min }} and {{ max }}.'
    )]
    private ?int $releaseYear = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'This field is required.')]
    #[Assert\Range(
        min: 30,
        max: 600,
        notInRangeMessage: 'The duration must be between {{ min }} and {{ max }} minutes.'
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function getCurrentYear(): int
    {
        return (int) date('Y');
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): static
    {
        $this->posterPath = $posterPath;

        return $this;
    }
}
