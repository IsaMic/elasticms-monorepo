<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220502132104 extends AbstractMigration
{
    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform
            && !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        $this->addSql('CREATE TABLE asset_storage (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, hash VARCHAR(1024) NOT NULL, contents LONGBLOB NOT NULL, size BIGINT NOT NULL, confirmed TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_37945A62D1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form_submission (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created DATETIME NOT NULL, modified DATETIME NOT NULL, name VARCHAR(255) NOT NULL, instance VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', expire_date DATE DEFAULT NULL, label VARCHAR(255) NOT NULL, process_try_counter INT DEFAULT 0 NOT NULL, process_id VARCHAR(255) DEFAULT NULL, process_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form_submission_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', form_submission_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created DATETIME NOT NULL, modified DATETIME NOT NULL, file LONGBLOB NOT NULL, filename VARCHAR(255) NOT NULL, form_field VARCHAR(255) NOT NULL, mime_type VARCHAR(1024) NOT NULL, size BIGINT NOT NULL, INDEX IDX_AEFF00A6422B0E0C (form_submission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log_message (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created DATETIME NOT NULL, modified DATETIME NOT NULL, message LONGTEXT NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL, channel VARCHAR(255) NOT NULL, extra LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', formatted LONGTEXT NOT NULL, username VARCHAR(255) DEFAULT NULL, impersonator VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE form_submission_file ADD CONSTRAINT FK_AEFF00A6422B0E0C FOREIGN KEY (form_submission_id) REFERENCES form_submission (id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform
            && !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        $this->addSql('ALTER TABLE form_submission_file DROP FOREIGN KEY FK_AEFF00A6422B0E0C');
        $this->addSql('DROP TABLE asset_storage');
        $this->addSql('DROP TABLE form_submission');
        $this->addSql('DROP TABLE form_submission_file');
        $this->addSql('DROP TABLE log_message');
    }
}
