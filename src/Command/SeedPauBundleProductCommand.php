<?php

namespace App\Command;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Product\PauBundleProductCatalog;
use App\Service\Product\ProductDownloadStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedPauBundleProductCommand extends Command
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private ProductDownloadStorage $downloadStorage,
        private PauBundleProductCatalog $productCatalog
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:product:seed-pau-bundle')
            ->setDescription('Crea o actualiza un producto de pack PAU descargable.')
            ->addOption('product-code', null, InputOption::VALUE_REQUIRED, 'Código del producto PAU configurado.')
            ->addOption('stripe-product-id', null, InputOption::VALUE_REQUIRED, 'ID del producto en Stripe.')
            ->addOption('stripe-price-id', null, InputOption::VALUE_REQUIRED, 'ID del precio en Stripe.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productCode = (string) $input->getOption('product-code');
        $stripeProductId = (string) $input->getOption('stripe-product-id');
        $stripePriceId = (string) $input->getOption('stripe-price-id');

        if ($productCode === '' || $stripeProductId === '' || $stripePriceId === '') {
            $output->writeln('<error>Debes indicar --product-code, --stripe-product-id y --stripe-price-id.</error>');
            return Command::FAILURE;
        }

        $definition = $this->productCatalog->findByCode($productCode);
        if ($definition === null) {
            $output->writeln(\sprintf('<error>No existe ningún pack PAU configurado con código %s.</error>', $productCode));
            return Command::FAILURE;
        }

        $product = $this->productRepository->findOneBy(['code' => $definition->getCode()]) ?? new Product();
        $product
            ->setCode($definition->getCode())
            ->setTitle($definition->getTitle())
            ->setSlug($definition->getSlug())
            ->setDescription($definition->getDescription())
            ->setStripeProductId($stripeProductId)
            ->setStripePriceId($stripePriceId)
            ->setPriceCents($definition->getPriceCents())
            ->setCurrency($definition->getCurrency())
            ->setEnabled(true)
            ->setFiles($definition->getFiles());

        $missingFiles = $this->downloadStorage->findMissingFiles($product);
        if ($missingFiles !== []) {
            $output->writeln(\sprintf('<error>No se puede activar el producto. Faltan archivos en %s:</error>', $this->downloadStorage->getStorageDescription()));
            foreach ($missingFiles as $missingFile) {
                $output->writeln(\sprintf(' - %s', $missingFile));
            }

            return Command::FAILURE;
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $output->writeln(\sprintf('<info>Producto listo:</info> %s', $product->getSlug()));
        return Command::SUCCESS;
    }
}
