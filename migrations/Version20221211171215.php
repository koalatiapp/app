<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20221211171215 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Adds the API refresh token table.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('CREATE TABLE api_refresh_token (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_6F229419C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('DROP TABLE api_refresh_token');
	}
}
