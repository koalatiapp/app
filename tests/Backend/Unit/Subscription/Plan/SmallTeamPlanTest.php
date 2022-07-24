<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use PHPUnit\Framework\TestCase;

/**
 * @covers \SmallTeamPlan::
 */
class SmallTeamPlanTest extends TestCase
{
	/**
	 * @covers \SmallTeamPlan::isUpgradeComparedTo
	 */
	public function testIsUpgradeComparedTo(): void
	{
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new NoPlan()), 'SmallTeamPlan isUpgradeComparedTo NoPlan');
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new TrialPlan()), 'SmallTeamPlan isUpgradeComparedTo TrialPlan');
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new SoloPlan()), 'SmallTeamPlan isUpgradeComparedTo SoloPlan');
		$this->assertFalse((new SmallTeamPlan())->isUpgradeComparedTo(new SmallTeamPlan()), 'SmallTeamPlan isUpgradeComparedTo SmallTeamPlan');
		$this->assertFalse((new SmallTeamPlan())->isUpgradeComparedTo(new BusinessPlan()), 'SmallTeamPlan isUpgradeComparedTo BusinessPlan');
	}

	/**
	 * @covers \SmallTeamPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo(): void
	{
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new NoPlan()), 'SmallTeamPlan isDowngradeComparedTo NoPlan');
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new TrialPlan()), 'SmallTeamPlan isDowngradeComparedTo TrialPlan');
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new SoloPlan()), 'SmallTeamPlan isDowngradeComparedTo SoloPlan');
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new SmallTeamPlan()), 'SmallTeamPlan isDowngradeComparedTo SmallTeamPlan');
		$this->assertTrue((new SmallTeamPlan())->isDowngradeComparedTo(new BusinessPlan()), 'SmallTeamPlan isDowngradeComparedTo BusinessPlan');
	}
}
