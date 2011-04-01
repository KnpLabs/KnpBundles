<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20110401160547 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->_addSql('ALTER TABLE repo CHANGE description description VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->_addSql('ALTER TABLE repo CHANGE description description VARCHAR(255) NOT NULL');
    }
}
