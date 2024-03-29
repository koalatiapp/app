<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210316123650 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE project_member (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, INDEX IDX_67401132166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE project_member_user (project_member_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B9D40DEE64AB9629 (project_member_id), INDEX IDX_B9D40DEEA76ED395 (user_id), PRIMARY KEY(project_member_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
		$this->addSql('ALTER TABLE project_member_user ADD CONSTRAINT FK_B9D40DEE64AB9629 FOREIGN KEY (project_member_id) REFERENCES project_member (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project_member_user ADD CONSTRAINT FK_B9D40DEEA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE project_member_user DROP FOREIGN KEY FK_B9D40DEE64AB9629');
		$this->addSql('DROP TABLE project_member');
		$this->addSql('DROP TABLE project_member_user');
	}
}
