<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\NoPlan;
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
	public function testIsUpgradeComparedTo(): void
	{
		$this->assertTrue((new SoloPlan())->isUpgradeComparedTo(new NoPlan()), 'SoloPlan isUpgradeComparedTo NoPlan');
		$this->assertTrue((new SoloPlan())->isUpgradeComparedTo(new TrialPlan()), 'SoloPlan isUpgradeComparedTo TrialPlan');
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new SoloPlan()), 'SoloPlan isUpgradeComparedTo SoloPlan');
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new SmallTeamPlan()), 'SoloPlan isUpgradeComparedTo SmallTeamPlan');
		$this->assertFalse((new SoloPlan())->isUpgradeComparedTo(new BusinessPlan()), 'SoloPlan isUpgradeComparedTo BusinessPlan');
	}

	/**
	 * @covers \SoloPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo(): void
	{
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new NoPlan()), 'SoloPlan isDowngradeComparedTo NoPlan');
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new TrialPlan()), 'SoloPlan isDowngradeComparedTo TrialPlan');
		$this->assertFalse((new SoloPlan())->isDowngradeComparedTo(new SoloPlan()), 'SoloPlan isDowngradeComparedTo SoloPlan');
		$this->assertTrue((new SoloPlan())->isDowngradeComparedTo(new SmallTeamPlan()), 'SoloPlan isDowngradeComparedTo SmallTeamPlan');
		$this->assertTrue((new SoloPlan())->isDowngradeComparedTo(new BusinessPlan()), 'SoloPlan isDowngradeComparedTo BusinessPlan');
	}
}
