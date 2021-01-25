<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210125181222 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE page CHANGE url url VARCHAR(510) NOT NULL');
		$this->addSql('CREATE UNIQUE INDEX unique_url ON page (url)');
		$this->addSql('ALTER TABLE project_ignored_page RENAME INDEX idx_e2ee346166d1f9c TO IDX_FA01045D166D1F9C');
		$this->addSql('ALTER TABLE project_ignored_page RENAME INDEX idx_e2ee346c4663e4 TO IDX_FA01045DC4663E4');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP INDEX unique_url ON page');
		$this->addSql('ALTER TABLE page CHANGE url url LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
		$this->addSql('ALTER TABLE project_ignored_page RENAME INDEX idx_fa01045d166d1f9c TO IDX_E2EE346166D1F9C');
		$this->addSql('ALTER TABLE project_ignored_page RENAME INDEX idx_fa01045dc4663e4 TO IDX_E2EE346C4663E4');
	}
}
