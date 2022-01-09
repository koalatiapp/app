<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20220109144120 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE project_activity_record DROP FOREIGN KEY FK_F1730A61166D1F9C');
		$this->addSql('ALTER TABLE project_activity_record DROP FOREIGN KEY FK_F1730A61A76ED395');
		$this->addSql('ALTER TABLE project_activity_record ADD CONSTRAINT FK_F1730A61166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE project_activity_record ADD CONSTRAINT FK_F1730A61A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE project_activity_record DROP FOREIGN KEY FK_F1730A61166D1F9C');
		$this->addSql('ALTER TABLE project_activity_record DROP FOREIGN KEY FK_F1730A61A76ED395');
		$this->addSql('ALTER TABLE project_activity_record ADD CONSTRAINT FK_F1730A61166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE project_activity_record ADD CONSTRAINT FK_F1730A61A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
	}
}
