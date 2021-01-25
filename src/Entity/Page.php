<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_url", columns={"url"})})
 */
class Page
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private ?string $title;

	/**
	 * @ORM\Column(type="string", length=510)
	 */
	private string $url;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $dateUpdated;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private ?int $httpCode;

	public function __construct(string $url, ?string $title = null)
	{
		$this->url = $url;
		$this->title = $title;
		$this->dateCreated = new \DateTime();
		$this->dateUpdated = new \DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(?string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	public function getDateCreated(): ?\DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(\DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getDateUpdated(): ?\DateTimeInterface
	{
		return $this->dateUpdated;
	}

	public function setDateUpdated(\DateTimeInterface $dateUpdated): self
	{
		$this->dateUpdated = $dateUpdated;

		return $this;
	}

	public function getHttpCode(): ?int
	{
		return $this->httpCode;
	}

	public function setHttpCode(?int $httpCode): self
	{
		$this->httpCode = $httpCode;

		return $this;
	}
}
