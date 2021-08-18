<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210818004023 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE `user` CHANGE first_name first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE last_name last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`');
	}
}
