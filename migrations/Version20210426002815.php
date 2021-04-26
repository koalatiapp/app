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
final class Version20210426002815 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2166D1F9C');
		$this->addSql('DROP INDEX IDX_433224D2166D1F9C ON recommendation');
		$this->addSql('ALTER TABLE recommendation DROP project_id');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation ADD project_id INT NOT NULL');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('CREATE INDEX IDX_433224D2166D1F9C ON recommendation (project_id)');
	}
}
