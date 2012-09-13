<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Github\Exception\ApiLimitExceedException;

/**
 * @todo Remove this command on next week or so
 */
class MigrateUsersCommand extends ContainerAwareCommand
{
    private $owners;
    private $github;

    private $ownerTable = <<<EOF
CREATE TABLE owner (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, fullName VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, avatarUrl VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, score INT NOT NULL, discriminator VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, UNIQUE INDEX name_unique (name), PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE organization_developer (organization_id INT NOT NULL, developer_id INT NOT NULL, INDEX IDX_5DCC519C32C8A3DE (organization_id), INDEX IDX_5DCC519C64DD9267 (developer_id), PRIMARY KEY(organization_id, developer_id)) ENGINE = InnoDB;
ALTER TABLE organization_developer ADD CONSTRAINT FK_5DCC519C32C8A3DE FOREIGN KEY (organization_id) REFERENCES owner (id) ON DELETE CASCADE;
ALTER TABLE organization_developer ADD CONSTRAINT FK_5DCC519C64DD9267 FOREIGN KEY (developer_id) REFERENCES owner (id) ON DELETE CASCADE;
ALTER TABLE bundle DROP FOREIGN KEY bundle_ibfk_1;
EOF;

    private $afterMigration = <<<EOF
ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15A76ED395;
ALTER TABLE bundles_usage DROP FOREIGN KEY FK_1351C3D1D9BF196B;
DROP INDEX IDX_1351C3D1D9BF196B ON bundles_usage;
DROP INDEX user_id ON contribution;
DROP INDEX user_id ON bundle;
DROP INDEX full_name_unique ON bundle;
DROP TABLE user;
ALTER TABLE bundle CHANGE user_id owner_id INT NOT NULL, CHANGE username ownerName VARCHAR(127) NOT NULL, ADD ownerType VARCHAR(15) NOT NULL;
ALTER TABLE bundle ADD CONSTRAINT FK_A57B32FD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES owner (id);
CREATE INDEX IDX_A57B32FD7E3C61F9 ON bundle (owner_id);
CREATE UNIQUE INDEX full_name_unique ON bundle (ownerName, name);
ALTER TABLE bundles_usage DROP PRIMARY KEY;
ALTER TABLE bundles_usage CHANGE knpbundles_user_id knpbundles_owner_id INT NOT NULL;
ALTER TABLE bundles_usage ADD CONSTRAINT FK_1351C3D124476F28 FOREIGN KEY (knpbundles_owner_id) REFERENCES owner (id);
CREATE INDEX IDX_1351C3D124476F28 ON bundles_usage (knpbundles_owner_id);
ALTER TABLE bundles_usage ADD PRIMARY KEY (bundle_id, knpbundles_owner_id);
ALTER TABLE contribution DROP PRIMARY KEY;
ALTER TABLE contribution CHANGE user_id developer_id INT NOT NULL;
ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1564DD9267 FOREIGN KEY (developer_id) REFERENCES owner (id) ON DELETE CASCADE;
CREATE INDEX IDX_EA351E1564DD9267 ON contribution (developer_id);
ALTER TABLE contribution ADD PRIMARY KEY (bundle_id, developer_id);
EOF;

    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:migrate:users')
            ->addOption('users', 'u', InputOption::VALUE_OPTIONAL, 'how many users migrate per one cycle', 10)
            ->addOption('remove-not-existing', 'rne', InputOption::VALUE_OPTIONAL, 'remove users which are not exist on github', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $connection \Doctrine\DBAL\Connection
         * @var $github \Github\Client
         */
        $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        if ($connection->getDatabasePlatform()->getName() == 'sqlite') {
            $output->writeln(sprintf('[%s] This command can\'t be executed on <error>SQLite</error>!', $this->currentTime()));

            return 1;
        }

        $this->github = $github = $this->getContainer()->get('knp_bundles.github_client');

        $usersPerCycle = $input->getOption('users');

        $testFetch = $connection->fetchAssoc('SELECT * FROM user LIMIT 1');

        if (!isset($testFetch['is_migrated'])) {
            $connection->executeQuery($this->ownerTable);
            $connection->executeQuery('ALTER TABLE user ADD is_migrated VARCHAR(10) NULL;');
        }

        do {
            $oldUsers = $connection->fetchAll(sprintf('SELECT * FROM user WHERE is_migrated IS NULL LIMIT %d', $usersPerCycle));

            foreach ($oldUsers as $oldUser) {
                try {
                    $ghUser = $github->api('user')->show($oldUser['name']);
                } catch (ApiLimitExceedException $e) {
                    throw $e;
                } catch (\RuntimeException $e) {
                    sleep(10);
                    continue;
                }

                if (isset($ghUser['message']) && $ghUser['message'] == 'Not Found') {
                    if ($input->getOption('remove-not-existing')) {
                        $connection->executeQuery('DELETE user WHERE id = :user_id', array('user_id' => $oldUser['id']));
                    } else {
                        $connection->executeQuery('UPDATE user SET is_migrated = 1 WHERE id = :user_id', array('user_id' => $oldUser['id']));
                    }
                    continue;
                }

                $data = $oldUser;
                $data['discriminator'] = $ghUser['type'] == 'Organization' ? 'organization' : 'developer';
                $data = $this->migrateColumns($data, $ghUser);

                $this->owners[] = $data;
            }

            $connection->beginTransaction();
            try {
                foreach ($this->owners as $ownerData) {
                    $connection->insert('owner', $ownerData);
                    $connection->executeQuery('UPDATE user SET is_migrated = 1 WHERE id = :user_id', array('user_id' => $ownerData['id']));
                }

                $connection->commit();

                $output->writeln(sprintf('%d users has been successfully migrated', $usersPerCycle));
            } catch (\Exception $e) {
                $connection->rollback();
                $connection->close();

                throw $e;
            }

            $this->owners = array();
        } while (count($oldUsers) > 0);

        $connection->executeQuery($this->afterMigration);

        return 0;
    }

    private function migrateColumns($data, $ghUser)
    {
        $data['url'] = $data['blog'];
        $data['avatarUrl'] = isset($ghUser['avatar_url']) ? $ghUser['avatar_url'] : null;

        unset($data['blog'], $data['gravatarHash'], $data['is_migrated']);

        return $data;
    }
}
