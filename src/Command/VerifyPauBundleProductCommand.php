<?php

namespace App\Command;

use App\Repository\ProductRepository;
use App\Service\Product\PauBundleProductCatalog;
use App\Service\Product\ProductDownloadStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyPauBundleProductCommand extends Command
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductDownloadStorage $downloadStorage,
        private PauBundleProductCatalog $productCatalog
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:product:verify-pau-bundle')
            ->setDescription('Verifica que un pack PAU está listo para venderse.')
            ->addOption('product-code', null, InputOption::VALUE_REQUIRED, 'Código del producto PAU configurado.')
            ->addOption('stripe-product-id', null, InputOption::VALUE_REQUIRED, 'ID esperado del producto en Stripe.')
            ->addOption('stripe-price-id', null, InputOption::VALUE_REQUIRED, 'ID esperado del precio en Stripe.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productCode = (string) $input->getOption('product-code');
        if ($productCode === '') {
            $output->writeln('<error>Debes indicar --product-code.</error>');
            return Command::FAILURE;
        }

        $definition = $this->productCatalog->findByCode($productCode);
        if ($definition === null) {
            $output->writeln(\sprintf('<error>No existe ningún pack PAU configurado con código %s.</error>', $productCode));
            return Command::FAILURE;
        }

        $product = $this->productRepository->findOneBy(['code' => $definition->getCode()]);
        if ($product === null) {
            $output->writeln('<error>El producto no existe. Ejecuta primero app:product:seed-pau-bundle.</error>');
            return Command::FAILURE;
        }

        $errors = [];
        if (!$product->isEnabled()) {
            $errors[] = 'El producto está desactivado.';
        }

        if ($product->getPriceCents() !== $definition->getPriceCents() || $product->getCurrency() !== $definition->getCurrency()) {
            $errors[] = \sprintf(
                'El precio local es %d %s y debería ser %d %s.',
                $product->getPriceCents(),
                $product->getCurrency(),
                $definition->getPriceCents(),
                $definition->getCurrency()
            );
        }

        $expectedStripeProductId = (string) $input->getOption('stripe-product-id');
        if ($expectedStripeProductId !== '' && $product->getStripeProductId() !== $expectedStripeProductId) {
            $errors[] = \sprintf('El Stripe product ID es %s y debería ser %s.', $product->getStripeProductId(), $expectedStripeProductId);
        }

        $expectedStripePriceId = (string) $input->getOption('stripe-price-id');
        if ($expectedStripePriceId !== '' && $product->getStripePriceId() !== $expectedStripePriceId) {
            $errors[] = \sprintf('El Stripe price ID es %s y debería ser %s.', $product->getStripePriceId(), $expectedStripePriceId);
        }

        $missingFiles = $this->downloadStorage->findMissingFiles($product);
        foreach ($missingFiles as $missingFile) {
            $errors[] = \sprintf('No existe o no es legible el archivo %s dentro de %s.', $missingFile, $this->downloadStorage->getStorageDescription());
        }

        if ($errors !== []) {
            $output->writeln('<error>El producto no está listo para producción:</error>');
            foreach ($errors as $error) {
                $output->writeln(\sprintf(' - %s', $error));
            }

            return Command::FAILURE;
        }

        $output->writeln(\sprintf('<info>Producto verificado:</info> %s', $product->getSlug()));
        $output->writeln(\sprintf('Archivos: %d', \count($product->getFiles())));
        $output->writeln(\sprintf('Almacenamiento: %s', $this->downloadStorage->getStorageDescription()));

        return Command::SUCCESS;
    }
}
