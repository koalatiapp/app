<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210607021615 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE ignore_entry (id INT AUTO_INCREMENT NOT NULL, target_organization_id INT DEFAULT NULL, target_user_id INT DEFAULT NULL, target_project_id INT DEFAULT NULL, created_by_id INT NOT NULL, target_page_id INT DEFAULT NULL, date_created DATETIME NOT NULL, tool VARCHAR(255) NOT NULL, test VARCHAR(255) NOT NULL, recommendation_unique_name VARCHAR(255) NOT NULL, INDEX IDX_DC154C1B70C641BD (target_organization_id), INDEX IDX_DC154C1B6C066AFE (target_user_id), INDEX IDX_DC154C1B2481C70D (target_project_id), INDEX IDX_DC154C1BB03A8386 (created_by_id), INDEX IDX_DC154C1BC72EDA8F (target_page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B70C641BD FOREIGN KEY (target_organization_id) REFERENCES organization (id)');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B6C066AFE FOREIGN KEY (target_user_id) REFERENCES `user` (id)');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B2481C70D FOREIGN KEY (target_project_id) REFERENCES project (id)');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1BB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1BC72EDA8F FOREIGN KEY (target_page_id) REFERENCES page (id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE ignore_entry');
	}
}
