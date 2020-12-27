<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227181017 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
		$this->addSql('CREATE TABLE project (id INT NOT NULL, owner_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date_created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, url VARCHAR(512) NOT NULL, PRIMARY KEY(id))');
		$this->addSql('CREATE INDEX IDX_2FB3D0EE2B18554A ON project (owner_user_id)');
		$this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2B18554A FOREIGN KEY (owner_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE SCHEMA public');
		$this->addSql('DROP SEQUENCE project_id_seq CASCADE');
		$this->addSql('DROP TABLE project');
	}
}
