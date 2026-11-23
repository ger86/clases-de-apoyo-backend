<?php

namespace App\Command;

use App\Service\LeadMagnet\LeadFollowUpSender;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Run it from cron every hour. The limit keeps each run under the daily sending
 * cap of the mail account.
 */
#[AsCommand(
    name: 'app:lead-magnet:send-follow-ups',
    description: 'Envía los emails de seguimiento del pack gratuito de exámenes que toca enviar.'
)]
final class SendLeadFollowUpsCommand extends Command
{
    public function __construct(
        private LeadFollowUpSender $sender,
        private RouterInterface $router
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Máximo de emails en esta ejecución.', '50')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Dominio de los enlaces de los emails.', 'www.clasesdeapoyo.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        if ($limit < 1) {
            $output->writeln('<error>--limit debe ser mayor que 0.</error>');

            return Command::FAILURE;
        }

        // The console has no request, so the links in the emails need a host.
        $context = $this->router->getContext();
        $context->setHost((string) $input->getOption('host'));
        $context->setScheme('https');
        $context->setBaseUrl('');

        $sent = $this->sender->sendDue(new DateTimeImmutable(), $limit);
        $output->writeln(\sprintf('Emails de seguimiento enviados: %d', $sent));

        return Command::SUCCESS;
    }
}
