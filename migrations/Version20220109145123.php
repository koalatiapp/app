<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20220109145123 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE organization_invitation DROP FOREIGN KEY FK_1846F34DB79F4F04');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34DB79F4F04 FOREIGN KEY (inviter_id) REFERENCES `user` (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE organization_invitation DROP FOREIGN KEY FK_1846F34DB79F4F04');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34DB79F4F04 FOREIGN KEY (inviter_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
	}
}
