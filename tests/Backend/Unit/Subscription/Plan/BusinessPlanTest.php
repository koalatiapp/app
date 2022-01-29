<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\FreePlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BusinessPlan::
 */
class BusinessPlanTest extends TestCase
{
	/**
	 * @covers \BusinessPlan::isUpgradeComparedTo
	 */
	public function testIsUpgradeComparedTo()
	{
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new FreePlan()));
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new TrialPlan()));
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new SoloPlan()));
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new SmallTeamPlan()));
		$this->assertFalse((new BusinessPlan())->isUpgradeComparedTo(new BusinessPlan()));
	}

	/**
	 * @covers \BusinessPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo()
	{
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new FreePlan()));
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new TrialPlan()));
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new SoloPlan()));
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new SmallTeamPlan()));
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new BusinessPlan()));
	}
}
