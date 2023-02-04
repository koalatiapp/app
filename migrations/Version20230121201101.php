<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230121201101 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Add next & previous billing dates to users.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE user ADD previous_billing_date DATETIME DEFAULT NULL, ADD next_billing_date DATETIME DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE `user` DROP previous_billing_date, DROP next_billing_date');
	}
}
