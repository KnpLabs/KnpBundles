<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140703121726 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE activities ADD CONSTRAINT FK_B5F1AFE5F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES bundle (id)");
        $this->addSql("ALTER TABLE bundles_usage ADD CONSTRAINT FK_1351C3D124476F28 FOREIGN KEY (knpbundles_owner_id) REFERENCES owner (id)");
        $this->addSql("DROP INDEX date_repo ON score");
        $this->addSql("ALTER TABLE score ADD CONSTRAINT FK_32993751F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES bundle (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE activities DROP FOREIGN KEY FK_B5F1AFE5F1FAD9D3");
        $this->addSql("ALTER TABLE bundles_usage DROP FOREIGN KEY FK_1351C3D124476F28");
        $this->addSql("ALTER TABLE score DROP FOREIGN KEY FK_32993751F1FAD9D3");
        $this->addSql("CREATE UNIQUE INDEX date_repo ON score (date, bundle_id)");
    }
}
