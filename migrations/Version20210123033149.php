<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210123033149 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, url LONGTEXT NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, http_code INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE project_page (project_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_2D9B7E38166D1F9C (project_id), INDEX IDX_2D9B7E38C4663E4 (page_id), PRIMARY KEY(project_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE project_ignored_page (project_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_E2EE346166D1F9C (project_id), INDEX IDX_E2EE346C4663E4 (page_id), PRIMARY KEY(project_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP TABLE page');
		$this->addSql('DROP TABLE project_page');
		$this->addSql('DROP TABLE project_ignored_page');
	}
}
