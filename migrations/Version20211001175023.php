<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20211001175023 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE project_activity_record (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT NOT NULL, website_url VARCHAR(512) NOT NULL, date_created DATETIME NOT NULL, page_url VARCHAR(512) NOT NULL, tool VARCHAR(255) NOT NULL, INDEX IDX_F1730A61166D1F9C (project_id), INDEX IDX_F1730A61A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE project_activity_record');
	}
}
