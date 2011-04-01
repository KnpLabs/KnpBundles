<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20100713230712 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->_addSql('ALTER TABLE repo ADD websiteUrl VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->_addSql('ALTER TABLE repo DROP websiteUrl');
    }
}
