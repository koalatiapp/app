<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings("unused")
 */
final class Version20210810000736 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		$migrationDir = rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR);
		$sql = file_get_contents($migrationDir.DIRECTORY_SEPARATOR.'Version20210810000736.sql');
		$this->addSql($sql);
	}

	public function down(Schema $schema): void
	{
		$this->addSql("DELETE FROM `checklist_template`
			WHERE `is_public` = 1
			AND `owner_user_id` IS NULL
			AND `owner_organization_id` IS NULL
			AND `name` LIKE \"Koalati's Default Checklist\"
		");
	}
}
