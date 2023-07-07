<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230707172718 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Adds "use canonical page URLs" setting to projects';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE project ADD use_canonical_page_urls TINYINT(1) DEFAULT 1 NOT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE project DROP use_canonical_page_urls');
	}
}
