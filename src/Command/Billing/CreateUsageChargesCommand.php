<?php

namespace App\Command\Billing;

use App\Repository\UserRepository;
use App\Subscription\UsageManager;
use App\Util\SelfHosting;
use Paddle\API;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
#[AsCommand(name: 'app:billing:create-usage-charges')]
class CreateUsageChargesCommand extends Command
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly UsageManager $usageManager,
		private readonly API $paddleApi,
		private readonly SelfHosting $selfHosting,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Creates one-time charges for users who have over-the-quota testing usage and whose usage cycle ended yesterday.');
		$this->addOption("dry-run", "d", InputOption::VALUE_NONE, "Runs the command and outputs as usual, but without actually creating the charges.");
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$isDryRun = $input->getOption("dry-run");

		if ($this->selfHosting->isSelfHosted()) {
			$io->caution("Usage billing is not available in self-hosting mode.");
		}

		$count = 0;
		$totalChargedAmount = 0;
		$failedCount = 0;
		$failedTotalAmount = 0;
		$users = $this->userRepository->findAll();
		$today = date("Y-m-d");

		foreach ($users as $user) {
			$usageManager = $this->usageManager->withUser($user);

			// If today is not the billing day for the user's previous usage cycle, it's not time to bill them.
			if ($today != $usageManager->getUsageCycleBillingDate("yesterday")->format("Y-m-d")) {
				continue;
			}

			$amountDue = ceil($usageManager->getUsageCost("yesterday") * 100) / 100;

			// Avoid super-petty charges.
			// No one likes them and they're not worth pursuing - especially with Paddle's fees (5% + 50¢).
			if ($amountDue <= 0.99) {
				continue;
			}

			$extraUsageUnits = $usageManager->getUsageUnitsOverQuota("yesterday");

			try {
				if (!$isDryRun) {
					$this->paddleApi->subscription()->createOneOffCharge(
						(int) $user->getPaddleSubscriptionId(),
						$amountDue,
						sprintf(
							"%s Page Tests over quota (%s - %s)",
							number_format($extraUsageUnits),
							$usageManager->getUsageCycleStartDate("yesterday")->format("m/d"),
							$usageManager->getUsageCycleEndDate("yesterday")->format("m/d"),
						),
					);
				}

				$count++;
				$totalChargedAmount += $amountDue;
			} catch (\Exception $exception) {
				$this->logger->critical($exception->getMessage(), $exception->getTrace());
				$io->error(sprintf("Failed to charge user with ID #{$user->getId()} $%s for their extra usage in the past cycle.", number_format($amountDue, 2)));

				$failedCount++;
				$failedTotalAmount = $amountDue;
			}
		}

		if ($count) {
			$verb = $isDryRun ? "Would have charged" : "Charged";
			$io->success(sprintf("$verb %d users a total of $%s for their extra usage in the past cycle.", $count, number_format($totalChargedAmount, 2)));
		}

		if ($failedCount) {
			$io->error(sprintf('Failed to charge %d users a total of $%s. Check logs and above output for more information.', $failedCount, number_format($failedTotalAmount, 2)));
		}

		if (!$count && !$failedCount) {
			$io->info("No users to charge for today.");
		}

		return Command::SUCCESS;
	}
}
