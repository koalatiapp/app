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
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2F6EC99AD FOREIGN KEY (parent_result_id) REFERENCES test_result (id)');
		$this->addSql('CREATE INDEX IDX_433224D2F6EC99AD ON recommendation (parent_result_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE recommendation_test_result (recommendation_id INT NOT NULL, test_result_id INT NOT NULL, INDEX IDX_D7D54E22853A2189 (test_result_id), INDEX IDX_D7D54E22D173940B (recommendation_id), PRIMARY KEY(recommendation_id, test_result_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('ALTER TABLE recommendation_test_result ADD CONSTRAINT FK_D7D54E22853A2189 FOREIGN KEY (test_result_id) REFERENCES test_result (id) ON UPDATE NO ACTION ON DELETE CASCADE');
		$this->addSql('ALTER TABLE recommendation_test_result ADD CONSTRAINT FK_D7D54E22D173940B FOREIGN KEY (recommendation_id) REFERENCES recommendation (id) ON UPDATE NO ACTION ON DELETE CASCADE');
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2F6EC99AD');
		$this->addSql('DROP INDEX IDX_433224D2F6EC99AD ON recommendation');
		$this->addSql('ALTER TABLE recommendation DROP parent_result_id');
	}
}
