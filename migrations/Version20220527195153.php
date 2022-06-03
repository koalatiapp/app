<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20220527195153 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE INDEX page_url_index ON page (url)');
		$this->addSql('ALTER TABLE tool_response CHANGE url url VARCHAR(510) NOT NULL');
		$this->addSql('CREATE INDEX page_url_index ON tool_response (tool, url)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP INDEX page_url_index ON page');
		$this->addSql('DROP INDEX page_url_index ON tool_response');
		$this->addSql('ALTER TABLE tool_response CHANGE url url VARCHAR(1024) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
	}
}
