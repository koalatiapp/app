<?php

namespace App\Tests\Backend\Unit\Subscription\Plan;

use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\Plan\TrialPlan;
use PHPUnit\Framework\TestCase;

/**
 * @covers \NoPlan::
 */
class NoPlanTest extends TestCase
{
	/**
	 * @covers \NoPlan::isUpgradeComparedTo
	 */
	public function testIsUpgradeComparedTo(): void
	{
		$this->assertFalse((new NoPlan())->isUpgradeComparedTo(new NoPlan()), 'NoPlan isUpgradeComparedTo NoPlan');
		$this->assertFalse((new NoPlan())->isUpgradeComparedTo(new TrialPlan()), 'NoPlan isUpgradeComparedTo TrialPlan');
		$this->assertFalse((new NoPlan())->isUpgradeComparedTo(new SoloPlan()), 'NoPlan isUpgradeComparedTo SoloPlan');
		$this->assertFalse((new NoPlan())->isUpgradeComparedTo(new SmallTeamPlan()), 'NoPlan isUpgradeComparedTo SmallTeamPlan');
		$this->assertFalse((new NoPlan())->isUpgradeComparedTo(new BusinessPlan()), 'NoPlan isUpgradeComparedTo BusinessPlan');
	}

	/**
	 * @covers \NoPlan::isDowngradeComparedTo
	 */
	public function testIsDowngradeComparedTo(): void
	{
		$this->assertFalse((new NoPlan())->isDowngradeComparedTo(new NoPlan()), 'NoPlan isDowngradeComparedTo NoPlan');
		$this->assertTrue((new NoPlan())->isDowngradeComparedTo(new TrialPlan()), 'NoPlan isDowngradeComparedTo TrialPlan');
		$this->assertTrue((new NoPlan())->isDowngradeComparedTo(new SoloPlan()), 'NoPlan isDowngradeComparedTo SoloPlan');
		$this->assertTrue((new NoPlan())->isDowngradeComparedTo(new SmallTeamPlan()), 'NoPlan isDowngradeComparedTo SmallTeamPlan');
		$this->assertTrue((new NoPlan())->isDowngradeComparedTo(new BusinessPlan()), 'NoPlan isDowngradeComparedTo BusinessPlan');
	}
}
