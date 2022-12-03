<?php

namespace App\Command\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Subscription\Plan\BusinessPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Enables creation of a user via the command line.
 */
class CreateUserCommand extends Command
{
	protected static $defaultName = 'app:security:create-user';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly EntityManagerInterface $entityManager,
		private readonly UserPasswordHasherInterface $passwordHasher,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Creates a new Koalati user.')
			->addArgument('email', InputArgument::OPTIONAL, 'Email address:')
			->addArgument('first_name', InputArgument::OPTIONAL, 'First name:')
			->addArgument('password', InputArgument::OPTIONAL, 'Password:')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$users = $this->userRepository->findAll();
		$existingEmails = array_map(fn (User $user) => strtolower($user->getEmail()), $users);

		$email = $input->getArgument("email");
		$firstName = trim((string) $input->getArgument("first_name"));
		$plainPassword = trim((string) $input->getArgument("password"));

		if (!$email) {
			$email = $io->ask("Email address", null, function (?string $email) use ($existingEmails) {
				if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
					throw new \RuntimeException("Invalid email address");
				}

				if (in_array($email, $existingEmails)) {
					throw new \RuntimeException("A user with this email address already exists.");
				}

				return trim($email);
			});
		}

		$requiredValidator = function (?string $value) {
			if (!strlen(trim($value))) {
				throw new \RuntimeException("This field is required.");
			}

			return trim($value);
		};

		if (!$firstName) {
			$firstName = $io->ask("First name", null, $requiredValidator);
		}

		if (!$plainPassword) {
			$plainPassword = $io->askHidden("Password", $requiredValidator);
		}

		$user = new User();
		$user
			->setSubscriptionPlan(BusinessPlan::UNIQUE_NAME)
			->setEmail($email)
			->setFirstName($firstName)
			->setPassword($this->passwordHasher->hashPassword($user, $plainPassword))
			->setIsVerified(true);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$io->success("The user has been created!");

		return Command::SUCCESS;
	}
}
