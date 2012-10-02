<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

use Github\Exception\ApiLimitExceedException;

/**
 * @todo Remove this command on next week or so
 */
class MigrateUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:migrate:users')
            ->addOption('users', 'u', InputOption::VALUE_OPTIONAL, 'how many users migrate per one cycle', 100)
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

        $usersPerCycle = $input->getOption('users');

        $testFetch = $connection->fetchAssoc('SELECT * FROM owner WHERE discriminator = "developer" LIMIT 1');
        if (!isset($testFetch['is_migrated'])) {
            $connection->executeQuery('ALTER TABLE owner ADD is_migrated VARCHAR(10) NULL, ADD githubId VARCHAR(255) NULL, ADD sensioId VARCHAR(255) NULL;');
        }
        unset($testFetch);

        $github = $this->getContainer()->get('knp_bundles.github_client');

        do {
            $oldUsers = $connection->fetchAll(sprintf('SELECT id, name FROM owner WHERE is_migrated IS NULL LIMIT %d', $usersPerCycle));

            foreach ($oldUsers as $oldUser) {
                try {
                    $ghUser = $github->api('user')->show($oldUser['name']);
                } catch (ApiLimitExceedException $e) {
                    throw $e;
                } catch (\RuntimeException $e) {
                    sleep(10);
                    continue;
                }

                $connection->executeQuery('UPDATE owner SET is_migrated = 1, githubId = :github_id WHERE id = :user_id', array('github_id' => isset($ghUser['login']) ? $ghUser['login'] : null, 'user_id' => $oldUser['id']));
            }

            $output->writeln(sprintf('%d users has been successfully migrated', $usersPerCycle));
        } while (count($oldUsers) > 0);

        $connection->executeQuery('ALTER TABLE owner DROP is_migrated;');

        return 0;
    }
}
