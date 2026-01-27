<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Jostkleigrewe\CookieConsentBundle\Entity\CookieConsentLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cookie-consent:prune-logs',
    description: 'Deletes cookie consent audit logs older than the configured retention period.',
    aliases: ['cookie-consent:cleanup'],
)]
final class PruneConsentLogsCommand extends Command
{
    /**
     * @param array{enabled: bool, level: string, anonymize_ip: bool, retention_days?: int|null} $logging
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly array $logging,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            null,
            InputOption::VALUE_OPTIONAL,
            'Retention window in days (overrides config).'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = $input->getOption('days');
        if ($days === null) {
            $days = $this->logging['retention_days'] ?? null;
        }

        if ($days === null) {
            $io->warning('No retention_days configured. Nothing to prune.');
            return Command::SUCCESS;
        }

        $days = (int) $days;
        if ($days <= 0) {
            $io->error('Retention days must be a positive integer.');
            return Command::FAILURE;
        }

        $cutoff = new \DateTimeImmutable(sprintf('-%d days', $days));

        $deleted = $this->entityManager->createQueryBuilder()
            ->delete(CookieConsentLog::class, 'log')
            ->where('log.decidedAt < :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();

        $io->success(sprintf('Deleted %d consent log entries older than %d days.', $deleted, $days));

        return Command::SUCCESS;
    }
}
