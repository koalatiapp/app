<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * @SuppressWarnings("unused")
 */
final class Version20210607024055 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation DROP is_ignored');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation ADD is_ignored TINYINT(1) NOT NULL');
	}
}
