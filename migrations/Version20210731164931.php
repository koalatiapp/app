<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210731164931 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE checklist (id INT AUTO_INCREMENT NOT NULL, template_id INT NOT NULL, project_id INT NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_5C696D2F5DA0FB8 (template_id), UNIQUE INDEX UNIQ_5C696D2F166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE checklist_template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_public TINYINT(1) NOT NULL, checklist_content JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, checklist_id INT NOT NULL, parent_group_id INT DEFAULT NULL, title LONGTEXT NOT NULL, description LONGTEXT NOT NULL, resource_urls LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_1F1B251EB16D08A7 (checklist_id), INDEX IDX_1F1B251E61997596 (parent_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE item_group (id INT AUTO_INCREMENT NOT NULL, checklist_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_47675F15B16D08A7 (checklist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP TABLE checklist');
		$this->addSql('DROP TABLE checklist_template');
		$this->addSql('DROP TABLE item');
		$this->addSql('DROP TABLE item_group');
	}
}
