<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210827120817 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP INDEX IDX_DC154C1BC72EDA8F ON ignore_entry');
		$this->addSql('ALTER TABLE ignore_entry CHANGE target_page_id page_id INT DEFAULT NULL');
		$this->addSql('CREATE INDEX IDX_DC154C1BC4663E4 ON ignore_entry (page_id)');
		$this->addSql('ALTER TABLE item CHANGE parent_group_id parent_group_id INT NOT NULL');
		$this->addSql('ALTER TABLE project_member CHANGE project_id project_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL');
		$this->addSql('DROP INDEX IDX_433224D2335FA941 ON recommendation');
		$this->addSql('ALTER TABLE recommendation CHANGE related_page_id page_id INT NOT NULL');
		$this->addSql('CREATE INDEX IDX_433224D2C4663E4 ON recommendation (page_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP INDEX IDX_DC154C1BC4663E4 ON ignore_entry');
		$this->addSql('ALTER TABLE ignore_entry CHANGE page_id target_page_id INT DEFAULT NULL');

		$this->addSql('CREATE INDEX IDX_DC154C1BC72EDA8F ON ignore_entry (target_page_id)');

		$this->addSql('ALTER TABLE item CHANGE parent_group_id parent_group_id INT DEFAULT NULL');

		$this->addSql('ALTER TABLE project_member CHANGE project_id project_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');

		$this->addSql('DROP INDEX IDX_433224D2C4663E4 ON recommendation');
		$this->addSql('ALTER TABLE recommendation CHANGE page_id related_page_id INT NOT NULL');

		$this->addSql('CREATE INDEX IDX_433224D2335FA941 ON recommendation (related_page_id)');
	}
}
