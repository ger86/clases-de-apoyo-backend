<?php

namespace App\Command;

use App\Service\SitemapService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class BuildSitemapCommand extends Command
{

    public function __construct(private SitemapService $sitemapService, private RouterInterface $router)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:sitemap:build')
            ->setDescription('Construye los sitemap del sitio');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = $this->router->getContext();
        $context->setHost('www.clasesdeapoyo.com');
        $context->setScheme('https');
        $context->setBaseUrl('');

        $this->sitemapService->buildSitemap();
        $output->writeln('done');
        return Command::SUCCESS;
    }
}
