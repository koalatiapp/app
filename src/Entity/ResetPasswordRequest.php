<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * @ORM\Entity(repositoryClass=ResetPasswordRequestRepository::class)
 */
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
	use ResetPasswordRequestTrait;

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private UserInterface $user;

	public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
	{
		$this->user = $user;
		$this->initialize($expiresAt, $selector, $hashedToken);
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): object
	{
		return $this->user;
	}
}
