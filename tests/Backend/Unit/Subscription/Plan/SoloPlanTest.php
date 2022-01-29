<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\FreePlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use PHPUnit\Framework\TestCase;

/**
 * @covers \SoloPlan::
 */
class SoloPlanTest extends TestCase
{
	/**
	 * @covers \SoloPlan::isUpgradeComparedTo
	 */
	public function testIsUpgradeComparedTo()
	{
		$this->assertTrue((new SoloPlan())->isUpgradeComparedTo(new FreePlan()));
		$this->assertTrue((new SoloPlan())->isUpgradeComparedTo(new TrialPlan()));
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new SoloPlan()));
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new SmallTeamPlan()));
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new BusinessPlan()));
	}

	/**
	 * @covers \SoloPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo()
	{
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new FreePlan()));
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new TrialPlan()));
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new SoloPlan()));
		$this->assertTrue((new SoloPlan())->isDowngradeComparedTo(new SmallTeamPlan()));
		$this->assertTrue((new SoloPlan())->isDowngradeComparedTo(new BusinessPlan()));
	}
}
