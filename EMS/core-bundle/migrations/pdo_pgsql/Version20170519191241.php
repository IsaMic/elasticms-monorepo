<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170519191241 extends AbstractMigration
{
    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $my_json_var = '[{"locale":"en","text":"<div class=\"box\"><div class=\"box-header with-border\"><h3 class=\"box-title\">Based on Symfony 3, Bootstrap 3 and AdminLTE</h3> </div> <div class=\"box-body\" style=\"display: block;\"><p>Visit <a href=\"http://www.elasticms.eu/\">elasticms.eu</a></p></div></div>"}]';

        $this->addSql('CREATE UNIQUE INDEX UNIQ_FF561896772E836A ON i18n (identifier)');
        $this->addSql('INSERT INTO i18n (id, created, modified, identifier, content) VALUES (nextval(\'i18n_id_seq\'), \'2017-05-19 21:04:48\', \'2017-05-19 21:21:27\', \'ems.documentation.body\', \'[{"locale":"en","text":"<div class=\"box\"><div class=\"box-header with-border\"><h3 class=\"box-title\">Based on Symfony 3, Bootstrap 3 and AdminLTE</h3> </div> <div class=\"box-body\" style=\"display: block;\"><p>Visit <a href=\"http://www.elasticms.eu/\">elasticms.eu</a></p></div></div>"}]\')');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql('DELETE FROM `i18n` WHERE `identifier` = \'ems.documentation.body\'');
        $this->addSql('DROP INDEX UNIQ_FF561896772E836A');
    }
}
