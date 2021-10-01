<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210409011512 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE recommendation (id INT AUTO_INCREMENT NOT NULL, related_page_id INT NOT NULL, completed_by_id INT DEFAULT NULL, template LONGTEXT NOT NULL, parameters JSON DEFAULT NULL, type VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_last_occured DATETIME NOT NULL, date_completed DATETIME DEFAULT NULL, is_completed TINYINT(1) NOT NULL, is_ignored TINYINT(1) NOT NULL, INDEX IDX_433224D2335FA941 (related_page_id), INDEX IDX_433224D285ECDE76 (completed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE recommendation_test_result (recommendation_id INT NOT NULL, test_result_id INT NOT NULL, INDEX IDX_D7D54E22D173940B (recommendation_id), INDEX IDX_D7D54E22853A2189 (test_result_id), PRIMARY KEY(recommendation_id, test_result_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2335FA941 FOREIGN KEY (related_page_id) REFERENCES page (id)');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D285ECDE76 FOREIGN KEY (completed_by_id) REFERENCES `user` (id)');
		$this->addSql('ALTER TABLE recommendation_test_result ADD CONSTRAINT FK_D7D54E22D173940B FOREIGN KEY (recommendation_id) REFERENCES recommendation (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE recommendation_test_result ADD CONSTRAINT FK_D7D54E22853A2189 FOREIGN KEY (test_result_id) REFERENCES test_result (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE recommendation_test_result DROP FOREIGN KEY FK_D7D54E22D173940B');
		$this->addSql('DROP TABLE recommendation');
		$this->addSql('DROP TABLE recommendation_test_result');
	}
}
