<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210419005418 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE recommendation_test_result');
		$this->addSql('ALTER TABLE recommendation ADD parent_result_id INT NOT NULL');
		$this->addSql('CREATE INDEX IDX_433224D2F6EC99AD ON recommendation (parent_result_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE recommendation_test_result (recommendation_id INT NOT NULL, test_result_id INT NOT NULL, INDEX IDX_D7D54E22853A2189 (test_result_id), INDEX IDX_D7D54E22D173940B (recommendation_id), PRIMARY KEY(recommendation_id, test_result_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');

		$this->addSql('DROP INDEX IDX_433224D2F6EC99AD ON recommendation');
		$this->addSql('ALTER TABLE recommendation DROP parent_result_id');
	}
}
