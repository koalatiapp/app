<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210413015014 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE project_ignored_page');
		$this->addSql('DROP TABLE project_page');
		$this->addSql('ALTER TABLE page ADD project_id INT DEFAULT NULL, ADD is_ignored TINYINT(1) NOT NULL');
		$this->addSql('CREATE INDEX IDX_140AB620166D1F9C ON page (project_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE project_ignored_page (project_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_FA01045D166D1F9C (project_id), INDEX IDX_FA01045DC4663E4 (page_id), PRIMARY KEY(project_id, page_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('CREATE TABLE project_page (project_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_2D9B7E38166D1F9C (project_id), INDEX IDX_2D9B7E38C4663E4 (page_id), PRIMARY KEY(project_id, page_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');

		$this->addSql('DROP INDEX IDX_140AB620166D1F9C ON page');
		$this->addSql('ALTER TABLE page DROP project_id, DROP is_ignored');
	}
}
