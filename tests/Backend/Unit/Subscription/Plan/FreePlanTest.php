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
	public function testIsUpgradeComparedTo(): void
	{
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new FreePlan()), 'FreePlan isUpgradeComparedTo FreePlan');
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new TrialPlan()), 'FreePlan isUpgradeComparedTo TrialPlan');
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new SoloPlan()), 'FreePlan isUpgradeComparedTo SoloPlan');
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new SmallTeamPlan()), 'FreePlan isUpgradeComparedTo SmallTeamPlan');
		$this->assertFalse((new FreePlan())->isUpgradeComparedTo(new BusinessPlan()), 'FreePlan isUpgradeComparedTo BusinessPlan');
	}

	/**
	 * @covers \FreePlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo(): void
	{
		$this->assertFalse((new FreePlan())->isDowngradeComparedTo(new FreePlan()), 'FreePlan isDowngradeComparedTo FreePlan');
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new TrialPlan()), 'FreePlan isDowngradeComparedTo TrialPlan');
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new SoloPlan()), 'FreePlan isDowngradeComparedTo SoloPlan');
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new SmallTeamPlan()), 'FreePlan isDowngradeComparedTo SmallTeamPlan');
		$this->assertTrue((new FreePlan())->isDowngradeComparedTo(new BusinessPlan()), 'FreePlan isDowngradeComparedTo BusinessPlan');
	}
}
