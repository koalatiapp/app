<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230407152855 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Allow activity logs without a target';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE activity_log CHANGE target target VARCHAR(512) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE activity_log CHANGE target target VARCHAR(512) NOT NULL');
	}
}
