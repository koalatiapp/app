<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20221028154326 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Adds email verification flag to Users.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE `user` DROP is_verified');
	}
}
