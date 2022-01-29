<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\FreePlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FreePlan::
 */
class FreePlanTest extends TestCase
{
	/**
	 * @covers \FreePlan::isUpgradeComparedTo
	 */
	public function testIsUpgradeComparedTo()
	{
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new FreePlan()));
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new TrialPlan()));
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new SoloPlan()));
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new SmallTeamPlan()));
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new BusinessPlan()));
	}

	/**
	 * @covers \FreePlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo()
	{
		$this->assertFalse((new FreePlan())->isDowngradeComparedTo(new FreePlan()));
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new TrialPlan()));
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new SoloPlan()));
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new SmallTeamPlan()));
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new BusinessPlan()));
	}
}
