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
	public function testIsUpgradeComparedTo(): void
	{
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new FreePlan()), 'BusinessPlan isUpgradeComparedTo FreePlan');
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new TrialPlan()), 'BusinessPlan isUpgradeComparedTo TrialPlan');
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new SoloPlan()), 'BusinessPlan isUpgradeComparedTo SoloPlan');
		$this->assertTrue((new BusinessPlan())->isUpgradeComparedTo(new SmallTeamPlan()), 'BusinessPlan isUpgradeComparedTo SmallTeamPlan');
		$this->assertFalse((new BusinessPlan())->isUpgradeComparedTo(new BusinessPlan()), 'BusinessPlan isUpgradeComparedTo BusinessPlan');
	}

	/**
	 * @covers \BusinessPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo(): void
	{
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new FreePlan()), 'BusinessPlan isDowngradeComparedTo FreePlan');
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new TrialPlan()), 'BusinessPlan isDowngradeComparedTo TrialPlan');
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new SoloPlan()), 'BusinessPlan isDowngradeComparedTo SoloPlan');
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new SmallTeamPlan()), 'BusinessPlan isDowngradeComparedTo SmallTeamPlan');
		$this->assertFalse((new BusinessPlan())->isDowngradeComparedTo(new BusinessPlan()), 'BusinessPlan isDowngradeComparedTo BusinessPlan');
	}
}
