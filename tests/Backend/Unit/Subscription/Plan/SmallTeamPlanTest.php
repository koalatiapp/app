<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\FreePlan;
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
	public function testIsUpgradeComparedTo()
	{
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new FreePlan()));
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new TrialPlan()));
		$this->assertTrue((new SmallTeamPlan())->isUpgradeComparedTo(new SoloPlan()));
		$this->assertFalse((new SmallTeamPlan())->isUpgradeComparedTo(new SmallTeamPlan()));
		$this->assertFalse((new SmallTeamPlan())->isUpgradeComparedTo(new BusinessPlan()));
	}

	/**
	 * @covers \SmallTeamPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo()
	{
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new FreePlan()));
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new TrialPlan()));
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new SoloPlan()));
		$this->assertFalse((new SmallTeamPlan())->isDowngradeComparedTo(new SmallTeamPlan()));
		$this->assertTrue((new SmallTeamPlan())->isDowngradeComparedTo(new BusinessPlan()));
	}
}
