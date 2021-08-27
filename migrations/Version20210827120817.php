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
		$this->addSql('ALTER TABLE checklist DROP FOREIGN KEY FK_5C696D2F166D1F9C');
		$this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1BC72EDA8F');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B2481C70D');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B6C066AFE');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B70C641BD');
		$this->addSql('DROP INDEX IDX_DC154C1BC72EDA8F ON ignore_entry');
		$this->addSql('ALTER TABLE ignore_entry CHANGE target_page_id page_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1BC4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B2481C70D FOREIGN KEY (target_project_id) REFERENCES project (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B6C066AFE FOREIGN KEY (target_user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B70C641BD FOREIGN KEY (target_organization_id) REFERENCES organization (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_DC154C1BC4663E4 ON ignore_entry (page_id)');
		$this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E61997596');
		$this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EB16D08A7');
		$this->addSql('ALTER TABLE item CHANGE parent_group_id parent_group_id INT NOT NULL');
		$this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E61997596 FOREIGN KEY (parent_group_id) REFERENCES item_group (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EB16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE item_group DROP FOREIGN KEY FK_47675F15B16D08A7');
		$this->addSql('ALTER TABLE item_group ADD CONSTRAINT FK_47675F15B16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE organization_invitation DROP FOREIGN KEY FK_1846F34D32C8A3DE');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE organization_member DROP FOREIGN KEY FK_756A2A8D32C8A3DE');
		$this->addSql('ALTER TABLE organization_member DROP FOREIGN KEY FK_756A2A8DA76ED395');
		$this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620166D1F9C');
		$this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project_member DROP FOREIGN KEY FK_67401132166D1F9C');
		$this->addSql('ALTER TABLE project_member DROP FOREIGN KEY FK_67401132A76ED395');
		$this->addSql('ALTER TABLE project_member CHANGE project_id project_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2335FA941');
		$this->addSql('DROP INDEX IDX_433224D2335FA941 ON recommendation');
		$this->addSql('ALTER TABLE recommendation CHANGE related_page_id page_id INT NOT NULL');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_433224D2C4663E4 ON recommendation (page_id)');
		$this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
		$this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D90DF3A30');
		$this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D90DF3A30 FOREIGN KEY (parent_response_id) REFERENCES tool_response (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE checklist DROP FOREIGN KEY FK_5C696D2F166D1F9C');
		$this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1BC4663E4');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B70C641BD');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B6C066AFE');
		$this->addSql('ALTER TABLE ignore_entry DROP FOREIGN KEY FK_DC154C1B2481C70D');
		$this->addSql('DROP INDEX IDX_DC154C1BC4663E4 ON ignore_entry');
		$this->addSql('ALTER TABLE ignore_entry CHANGE page_id target_page_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1BC72EDA8F FOREIGN KEY (target_page_id) REFERENCES page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B70C641BD FOREIGN KEY (target_organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B6C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE ignore_entry ADD CONSTRAINT FK_DC154C1B2481C70D FOREIGN KEY (target_project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('CREATE INDEX IDX_DC154C1BC72EDA8F ON ignore_entry (target_page_id)');
		$this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EB16D08A7');
		$this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E61997596');
		$this->addSql('ALTER TABLE item CHANGE parent_group_id parent_group_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EB16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E61997596 FOREIGN KEY (parent_group_id) REFERENCES item_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE item_group DROP FOREIGN KEY FK_47675F15B16D08A7');
		$this->addSql('ALTER TABLE item_group ADD CONSTRAINT FK_47675F15B16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE organization_invitation DROP FOREIGN KEY FK_1846F34D32C8A3DE');
		$this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE organization_member DROP FOREIGN KEY FK_756A2A8D32C8A3DE');
		$this->addSql('ALTER TABLE organization_member DROP FOREIGN KEY FK_756A2A8DA76ED395');
		$this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620166D1F9C');
		$this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE project_member DROP FOREIGN KEY FK_67401132166D1F9C');
		$this->addSql('ALTER TABLE project_member DROP FOREIGN KEY FK_67401132A76ED395');
		$this->addSql('ALTER TABLE project_member CHANGE project_id project_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE project_member ADD CONSTRAINT FK_67401132A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2C4663E4');
		$this->addSql('DROP INDEX IDX_433224D2C4663E4 ON recommendation');
		$this->addSql('ALTER TABLE recommendation CHANGE page_id related_page_id INT NOT NULL');
		$this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2335FA941 FOREIGN KEY (related_page_id) REFERENCES page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('CREATE INDEX IDX_433224D2335FA941 ON recommendation (related_page_id)');
		$this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
		$this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
		$this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D90DF3A30');
		$this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D90DF3A30 FOREIGN KEY (parent_response_id) REFERENCES tool_response (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
	}
}
