<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230407143855 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Add project relation to activity logs';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE activity_log ADD project_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
		$this->addSql('CREATE INDEX IDX_FD06F647166D1F9C ON activity_log (project_id)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647166D1F9C');
		$this->addSql('DROP INDEX IDX_FD06F647166D1F9C ON activity_log');
		$this->addSql('ALTER TABLE activity_log DROP project_id');
	}
}
