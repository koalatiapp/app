<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20220211222700 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, project_id INT NOT NULL, thread_id INT DEFAULT NULL, checklist_item_id INT DEFAULT NULL, content LONGTEXT NOT NULL, date_created DATETIME NOT NULL, author_name VARCHAR(255) DEFAULT NULL, is_resolved TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C166D1F9C (project_id), INDEX IDX_9474526CE2904019 (thread_id), INDEX IDX_9474526C7E0892A4 (checklist_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP TABLE comment');
	}
}
