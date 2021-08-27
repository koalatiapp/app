<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20210827121708 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE checklist_template DROP FOREIGN KEY FK_CA6463412B18554A');
		$this->addSql('ALTER TABLE checklist_template DROP FOREIGN KEY FK_CA646341AF5EABE9');
		$this->addSql('ALTER TABLE checklist_template ADD CONSTRAINT FK_CA6463412B18554A FOREIGN KEY (owner_user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE checklist_template ADD CONSTRAINT FK_CA646341AF5EABE9 FOREIGN KEY (owner_organization_id) REFERENCES organization (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE2B18554A');
		$this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEAF5EABE9');
		$this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2B18554A FOREIGN KEY (owner_user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEAF5EABE9 FOREIGN KEY (owner_organization_id) REFERENCES organization (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE checklist_template DROP FOREIGN KEY FK_CA6463412B18554A');
		$this->addSql('ALTER TABLE checklist_template DROP FOREIGN KEY FK_CA646341AF5EABE9');
		$this->addSql('ALTER TABLE checklist_template ADD CONSTRAINT FK_CA6463412B18554A FOREIGN KEY (owner_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE checklist_template ADD CONSTRAINT FK_CA646341AF5EABE9 FOREIGN KEY (owner_organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE2B18554A');
		$this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEAF5EABE9');
		$this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2B18554A FOREIGN KEY (owner_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEAF5EABE9 FOREIGN KEY (owner_organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
	}
}
