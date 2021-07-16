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
final class Version20210715221147 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE organization_invitation ADD hash VARCHAR(255) NOT NULL');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE organization_invitation DROP hash');
	}
}
