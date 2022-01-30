<?php

namespace App\Command\Email;

use App\Entity\UserMetadata;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendTrialNoticesCommand extends Command
{
	protected static $defaultName = 'app:email:trial-ending-notices';
	protected const TIME_BEFORE_TRIAL_EXPIRES = '+2 days';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UserRepository $userRepository,
		private EntityManagerInterface $entityManager,
		private MailerInterface $mailer,
		private TranslatorInterface $translator,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Send email notices to users whose free trial is about to end');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$count = 0;
		$users = $this->userRepository->findAllTrialsExpiringSoon(self::TIME_BEFORE_TRIAL_EXPIRES);

		foreach ($users as $user) {
			// If we already sent a notice to a user, no need to send another
			if ($user->getMetadataValue(UserMetadata::TRIAL_ENDING_NOTICE_SENT)) {
				continue;
			}

			// Send a notice to the user via email
			$email = (new TemplatedEmail())
				->to(new Address($user->getEmail(), $user->getFirstName()))
				->subject($this->translator->trans('email.trial_ending_soon.subject'))
				->htmlTemplate('email/trial_ending_soon.html.twig')
				->context(['user' => $user]);
			$this->mailer->send($email);

			// Set a flag to prevent sending the same email twice
			$user->setMetadata(UserMetadata::TRIAL_ENDING_NOTICE_SENT, '1');

			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$count++;
		}

		$io->success(sprintf('Notified %d users that their trial is expiring soon.', $count));

		return Command::SUCCESS;
	}
}
