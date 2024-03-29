<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210715211644 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE organization_invitation (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, inviter_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_expired DATETIME NOT NULL, date_used DATETIME DEFAULT NULL, INDEX IDX_1846F34D32C8A3DE (organization_id), INDEX IDX_1846F34DB79F4F04 (inviter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34DB79F4F04 FOREIGN KEY (inviter_id) REFERENCES `user` (id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE organization_invitation');
	}
}
