<?php

namespace App\Tests\Backend\Unit\Subscription;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exception\NonExistantSubscriptionPlanException;
use App\Subscription\Plan\BusinessAnnualPlan;
use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\SmallTeamAnnualPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloAnnualPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use App\Subscription\PlanManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \PlanManager::
 */
class PlanManagerTest extends KernelTestCase
{
	protected PlanManager $planManager;

	public function setUp(): void
	{
		$this->planManager = $this->getContainer()->get(PlanManager::class);
	}

	/**
	 * @covers \PlanManager::getAvailablePlans
	 */
	public function testGetAvailablePlans(): void
	{
		$availablePlans = $this->planManager->getAvailablePlans();

		$this->assertArrayHasKey('Trial', $availablePlans);
		$this->assertArrayHasKey('NoSubscription', $availablePlans);
		$this->assertArrayHasKey('Solo', $availablePlans);
		$this->assertArrayHasKey('SoloAnnual', $availablePlans);
		$this->assertArrayHasKey('SmallTeam', $availablePlans);
		$this->assertArrayHasKey('SmallTeamAnnual', $availablePlans);
		$this->assertArrayHasKey('Business', $availablePlans);
		$this->assertArrayHasKey('BusinessAnnual', $availablePlans);

		$this->assertEquals($availablePlans['Trial']::class, TrialPlan::class);
		$this->assertEquals($availablePlans['NoSubscription']::class, NoPlan::class);
		$this->assertEquals($availablePlans['Solo']::class, SoloPlan::class);
		$this->assertEquals($availablePlans['SoloAnnual']::class, SoloAnnualPlan::class);
		$this->assertEquals($availablePlans['SmallTeam']::class, SmallTeamPlan::class);
		$this->assertEquals($availablePlans['SmallTeamAnnual']::class, SmallTeamAnnualPlan::class);
		$this->assertEquals($availablePlans['Business']::class, BusinessPlan::class);
		$this->assertEquals($availablePlans['BusinessAnnual']::class, BusinessAnnualPlan::class);
	}

	/**
	 * @covers \PlanManager::getPlanFromUniqueName
	 */
	public function testGetPlanFromUniqueName(): void
	{
		$this->assertEquals($this->planManager->getPlanFromUniqueName('Trial')::class, TrialPlan::class);
		$this->assertEquals($this->planManager->getPlanFromUniqueName('NoSubscription')::class, NoPlan::class);
		$this->assertEquals($this->planManager->getPlanFromUniqueName('Solo')::class, SoloPlan::class);
		$this->assertEquals($this->planManager->getPlanFromUniqueName('SmallTeam')::class, SmallTeamPlan::class);
		$this->assertEquals($this->planManager->getPlanFromUniqueName('Business')::class, BusinessPlan::class);

		$this->expectWarning();
		$this->planManager->getPlanFromUniqueName('Unknown');
	}

	/**
	 * @covers \PlanManager::getPlanFromEntity
	 */
	public function testGetPlanFromEntity(): void
	{
		$user = new User();
		$user->setSubscriptionPlan('Solo');

		$this->assertEquals($this->planManager->getPlanFromEntity($user)::class, SoloPlan::class, 'Get plan from a user');

		$organization = new Organization();
		$membership = new OrganizationMember($organization, $user, OrganizationMember::ROLE_OWNER);
		$organization->addMember($membership);

		$this->assertEquals($this->planManager->getPlanFromEntity($organization)::class, SoloPlan::class, 'Get plan from an organization');
	}

	/**
	 * @covers \PlanManager::getPlanFromPaddleId
	 */
	public function testGetPlanFromPaddleId(): void
	{
		$this->assertEquals($this->planManager->getPlanFromPaddleId('738679')::class, NoPlan::class, 'Get NoPlan from Paddle ID');
		$this->assertEquals($this->planManager->getPlanFromPaddleId('664974')::class, SoloPlan::class, 'Get SoloPlan from Paddle ID');
		$this->assertEquals($this->planManager->getPlanFromPaddleId('664975')::class, SmallTeamPlan::class, 'Get SmallTeamPlan from Paddle ID');
		$this->assertEquals($this->planManager->getPlanFromPaddleId('664976')::class, BusinessPlan::class, 'Get BusinessPlan from Paddle ID');

		$this->expectException(NonExistantSubscriptionPlanException::class);
		$this->planManager->getPlanFromPaddleId('123456');
	}
}
