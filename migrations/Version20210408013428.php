<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210408013428 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE test_result (id INT AUTO_INCREMENT NOT NULL, parent_response_id INT NOT NULL, unique_name VARCHAR(255) NOT NULL, title VARCHAR(512) NOT NULL, description LONGTEXT NOT NULL, weight DOUBLE PRECISION DEFAULT NULL, score DOUBLE PRECISION NOT NULL, snippets LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', data_table LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_84B3C63D90DF3A30 (parent_response_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE tool_response (id INT AUTO_INCREMENT NOT NULL, tool VARCHAR(255) NOT NULL, url VARCHAR(1024) NOT NULL, date_received DATETIME NOT NULL, processing_time INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D90DF3A30 FOREIGN KEY (parent_response_id) REFERENCES tool_response (id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D90DF3A30');
		$this->addSql('DROP TABLE test_result');
		$this->addSql('DROP TABLE tool_response');
	}
}
