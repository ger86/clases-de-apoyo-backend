<?php

namespace App\Command;

use App\Service\LeadMagnet\FreeExamPack;
use App\Service\Product\ProductDownloadStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:lead-magnet:verify',
    description: 'Comprueba que los PDF del pack gratuito de exámenes están en S3.'
)]
final class VerifyFreeExamPackCommand extends Command
{
    public function __construct(
        private FreeExamPack $freeExamPack,
        private ProductDownloadStorage $downloadStorage
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\sprintf('Almacenamiento: %s', $this->downloadStorage->getStorageDescription()));

        $missing = 0;
        foreach ($this->freeExamPack->getFiles() as $file) {
            if ($this->downloadStorage->createDownloadUrl($file) === null) {
                $output->writeln(\sprintf('<error>Falta %s</error>', $file['path']));
                ++$missing;
                continue;
            }

            $output->writeln(\sprintf('OK %s', $file['path']));
        }

        if ($missing > 0) {
            $output->writeln('<error>El pack gratuito no está listo: sube los PDF que faltan.</error>');

            return Command::FAILURE;
        }

        $output->writeln('El pack gratuito está listo.');

        return Command::SUCCESS;
    }
}
