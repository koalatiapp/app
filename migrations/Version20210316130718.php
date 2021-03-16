<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210316130718 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE project_member_user');
		$this->addSql('ALTER TABLE project_member ADD user_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
		$this->addSql('CREATE INDEX IDX_67401132A76ED395 ON project_member (user_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE project_member_user (project_member_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B9D40DEE64AB9629 (project_member_id), INDEX IDX_B9D40DEEA76ED395 (user_id), PRIMARY KEY(project_member_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('ALTER TABLE project_member_user ADD CONSTRAINT FK_B9D40DEE64AB9629 FOREIGN KEY (project_member_id) REFERENCES project_member (id) ON UPDATE NO ACTION ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project_member_user ADD CONSTRAINT FK_B9D40DEEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project_member DROP FOREIGN KEY FK_67401132A76ED395');
		$this->addSql('DROP INDEX IDX_67401132A76ED395 ON project_member');
		$this->addSql('ALTER TABLE project_member DROP user_id');
	}
}
