<?php

namespace App\Command;

use App\Repository\ProductRepository;
use App\Service\Product\ProductDownloadStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyMadridMathPackProductCommand extends Command
{
    private const PRODUCT_CODE = 'pau_matematicas_ii_madrid_1994_2025';
    private const EXPECTED_PRICE_CENTS = 1499;
    private const EXPECTED_CURRENCY = 'eur';

    public function __construct(
        private ProductRepository $productRepository,
        private ProductDownloadStorage $downloadStorage
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:product:verify-madrid-math-pack')
            ->setDescription('Verifica que el pack PAU Matemáticas II Madrid está listo para venderse.')
            ->addOption('stripe-product-id', null, InputOption::VALUE_REQUIRED, 'ID esperado del producto en Stripe.')
            ->addOption('stripe-price-id', null, InputOption::VALUE_REQUIRED, 'ID esperado del precio en Stripe.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $product = $this->productRepository->findOneBy(['code' => self::PRODUCT_CODE]);
        if ($product === null) {
            $output->writeln('<error>El producto no existe. Ejecuta primero app:product:seed-madrid-math-pack.</error>');

            return Command::FAILURE;
        }

        $errors = [];
        if (!$product->isEnabled()) {
            $errors[] = 'El producto está desactivado.';
        }

        if ($product->getPriceCents() !== self::EXPECTED_PRICE_CENTS || $product->getCurrency() !== self::EXPECTED_CURRENCY) {
            $errors[] = \sprintf(
                'El precio local es %d %s y debería ser %d %s.',
                $product->getPriceCents(),
                $product->getCurrency(),
                self::EXPECTED_PRICE_CENTS,
                self::EXPECTED_CURRENCY
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
