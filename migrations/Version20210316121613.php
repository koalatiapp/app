<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210316121613 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE project ADD owner_organization_id INT DEFAULT NULL');
		$this->addSql('CREATE INDEX IDX_2FB3D0EEAF5EABE9 ON project (owner_organization_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP INDEX IDX_2FB3D0EEAF5EABE9 ON project');
		$this->addSql('ALTER TABLE project DROP owner_organization_id');
	}
}
