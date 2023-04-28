<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20230408125438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds on delete set null constraint to activity log relations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647166D1F9C');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F64732C8A3DE');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647A76ED395');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F64732C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647A76ED395');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F64732C8A3DE');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647166D1F9C');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F64732C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
