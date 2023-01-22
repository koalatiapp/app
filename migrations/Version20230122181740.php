<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230122181740 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Adds quota management properties/columns to users.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE user ADD allows_page_tests_over_quota TINYINT(1) DEFAULT 0 NOT NULL, ADD quota_exceedance_spending_limit DOUBLE PRECISION DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE `user` DROP allows_page_tests_over_quota, DROP quota_exceedance_spending_limit');
	}
}
