<?php

namespace App\Entity;

use App\Repository\ProjectMemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ProjectMemberRepository::class)
 */
class ProjectMember
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="teamMembers")
	 * @Groups({"default"})
	 */
	private ?Project $project;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projectLinks")
	 * @Groups({"default"})
	 */
	private ?User $user;

	public function __construct()
	{
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getProject(): ?Project
	{
		return $this->project;
	}

	public function setProject(?Project $project): self
	{
		$this->project = $project;

		return $this;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;

		return $this;
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function addUser(User $user): self
	{
		$this->user = $user;

		return $this;
	}
}
