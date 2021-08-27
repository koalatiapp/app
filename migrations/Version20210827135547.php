<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210827135547 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2F6EC99AD');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2F6EC99AD FOREIGN KEY (parent_result_id) REFERENCES test_result (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2F6EC99AD');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2F6EC99AD FOREIGN KEY (parent_result_id) REFERENCES test_result (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
	}
}
