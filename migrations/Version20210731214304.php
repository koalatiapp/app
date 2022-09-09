<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * @SuppressWarnings("unused")
 */
final class Version20210731214304 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE checklist_template ADD owner_user_id INT DEFAULT NULL, ADD owner_organization_id INT DEFAULT NULL');
		$this->addSql('CREATE INDEX IDX_CA6463412B18554A ON checklist_template (owner_user_id)');
		$this->addSql('CREATE INDEX IDX_CA646341AF5EABE9 ON checklist_template (owner_organization_id)');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs

		$this->addSql('DROP INDEX IDX_CA6463412B18554A ON checklist_template');
		$this->addSql('DROP INDEX IDX_CA646341AF5EABE9 ON checklist_template');
		$this->addSql('ALTER TABLE checklist_template DROP owner_user_id, DROP owner_organization_id');
	}
}
