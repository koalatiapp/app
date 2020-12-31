<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201225035242 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE `user` (id INT NOT NULL AUTO_INCREMENT, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, job_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON `user` (email)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE SCHEMA public');
		$this->addSql('DROP TABLE `user`');
	}
}
