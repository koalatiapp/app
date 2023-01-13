<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230113141054 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Add direct relation between recommendations and projects.';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE recommendation ADD project_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
		$this->addSql('CREATE INDEX IDX_433224D2166D1F9C ON recommendation (project_id)');
		$this->addSql('UPDATE `recommendation` R LEFT JOIN `page` P ON P.`id` = R.`page_id` SET R.`project_id` = P.`project_id`');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2166D1F9C');
		$this->addSql('DROP INDEX IDX_433224D2166D1F9C ON recommendation');
		$this->addSql('ALTER TABLE recommendation DROP project_id');
	}
}
