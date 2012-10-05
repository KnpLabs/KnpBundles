<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;
use Doctrine\ORM\Query\ResultSetMapping;

class KbUpdateTrendsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:update:trends')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('knp_bundles.entity_manager');

        if ($em->getConnection()->getDatabasePlatform()->getName() == 'sqlite') {
            $output->writeln(sprintf('[%s] This command can\'t be executed on <error>SQLite</error>!', date('d-m-y H:i:s')));

            return 1;
        }

        $em->getConnection()->beginTransaction();
        try {
            $nbRows = $this->updateTrends();
            $output->writeln(sprintf('[%s] <info>%s</info> rows updated', date('d-m-y H:i:s'), $nbRows));

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $output->writeln(sprintf('[%s] <error>Rollbacking</error> because of %s', date('d-m-y H:i:s'), $e));
            $em->getConnection()->rollback();
            $em->close();

            return 1;
        }

        return 0;
    }

    private function updateTrends()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Reset trends
        $q = $em->createQuery('UPDATE Knp\Bundle\KnpBundlesBundle\Entity\Bundle bundle SET bundle.trend1 = 0');
        $q->execute();

        $query = <<<EOF
UPDATE bundle

JOIN (
    SELECT date, bundle_id,
    (
        SELECT current.value - value AS diff
        FROM score
        WHERE bundle_id = current.bundle_id
        AND date < current.date
        ORDER BY date DESC
        LIMIT 1
    ) AS diff
    FROM score AS current
    WHERE date = CURRENT_DATE
) score
  ON score.bundle_id = bundle.id
  AND score.diff > :minDiff

SET trend1 = score.diff
WHERE score >= :minThreshold
EOF;

        $minDiff = $this->getContainer()->getParameter('knp_bundles.trending_bundle.min_score_diff');
        $minThreshold = $this->getContainer()->getParameter('knp_bundles.trending_bundle.min_score_threshold');
        $nbRows = $em->getConnection()->executeUpdate($query, array('minDiff' => $minDiff, 'minThreshold' => $minThreshold));

        return $nbRows;
    }
}
