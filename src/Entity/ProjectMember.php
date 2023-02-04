<?php

namespace App\Entity;

use App\Repository\ProjectMemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectMemberRepository::class)]
class ProjectMember
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'teamMembers')]
	#[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	private ?Project $project = null;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projectLinks')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	private ?User $user = null;

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
