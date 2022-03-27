<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class Version20220315131443 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE comment ADD text_content LONGTEXT NOT NULL');
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE checklist_template CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE description description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE comment DROP text_content, CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE author_name author_name VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE ignore_entry CHANGE tool tool VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE test test VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE recommendation_unique_name recommendation_unique_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE recommendation_title recommendation_title VARCHAR(512) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE item CHANGE title title LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE description description LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE resource_urls resource_urls LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci` COMMENT \'(DC2Type:array)\'');
		$this->addSql('ALTER TABLE item_group CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE messenger_messages CHANGE body body LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE headers headers LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE queue_name queue_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE organization CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE slug slug VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE organization_invitation CHANGE first_name first_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE email email VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE hash hash VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE page CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE url url VARCHAR(510) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE project CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE url url VARCHAR(512) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE disabled_tools disabled_tools LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci` COMMENT \'(DC2Type:array)\'');
		$this->addSql('ALTER TABLE project_activity_record CHANGE website_url website_url VARCHAR(512) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE page_url page_url VARCHAR(512) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE tool tool VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE recommendation CHANGE template template LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE unique_name unique_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE reset_password_request CHANGE selector selector VARCHAR(20) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE hashed_token hashed_token VARCHAR(100) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE test_result CHANGE unique_name unique_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE title title VARCHAR(512) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE description description LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE snippets snippets LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci` COMMENT \'(DC2Type:array)\', CHANGE data_table data_table LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci` COMMENT \'(DC2Type:array)\'');
		$this->addSql('ALTER TABLE tool_response CHANGE tool tool VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE url url VARCHAR(1024) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(180) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE first_name first_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE subscription_plan subscription_plan VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE upcoming_subscription_plan upcoming_subscription_plan VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
		$this->addSql('ALTER TABLE user_metadata CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE value value VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
	}
}
