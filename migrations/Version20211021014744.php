<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20211021014744 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user ADD upcoming_subscription_plan VARCHAR(255) DEFAULT NULL, CHANGE subscription_end_date subscription_change_date DATETIME DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE `user` DROP upcoming_subscription_plan, CHANGE subscription_change_date subscription_end_date DATETIME DEFAULT NULL');
	}
}
