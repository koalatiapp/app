<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230407143131 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Adds activity logs table';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('CREATE TABLE activity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, data JSON DEFAULT NULL, date_created DATETIME NOT NULL, target VARCHAR(512) NOT NULL, is_internal TINYINT(1) NOT NULL, INDEX IDX_FD06F647A76ED395 (user_id), INDEX IDX_FD06F64732C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
		$this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F64732C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('DROP TABLE activity_log');
	}
}
