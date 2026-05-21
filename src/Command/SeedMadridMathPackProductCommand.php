<?php

namespace App\Command;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Product\ProductDownloadStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedMadridMathPackProductCommand extends Command
{
    private const PRODUCT_CODE = 'pau_matematicas_ii_madrid_1994_2025';

    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private ProductDownloadStorage $downloadStorage
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:product:seed-madrid-math-pack')
            ->setDescription('Crea o actualiza el producto del pack PAU Matemáticas II Madrid.')
            ->addOption('stripe-product-id', null, InputOption::VALUE_REQUIRED, 'ID del producto en Stripe.')
            ->addOption('stripe-price-id', null, InputOption::VALUE_REQUIRED, 'ID del precio en Stripe.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stripeProductId = (string) $input->getOption('stripe-product-id');
        $stripePriceId = (string) $input->getOption('stripe-price-id');

        if ($stripeProductId === '' || $stripePriceId === '') {
            $output->writeln('<error>Debes indicar --stripe-product-id y --stripe-price-id.</error>');
            return Command::FAILURE;
        }

        $product = $this->productRepository->findOneBy(['code' => self::PRODUCT_CODE]) ?? new Product();
        $product
            ->setCode(self::PRODUCT_CODE)
            ->setTitle('Pack PAU Matemáticas II Madrid 1994-2025')
            ->setSlug('pau-matematicas-ii-madrid-1994-2025')
            ->setDescription(<<<HTML
<p>Prepara la PAU/EvAU de Matemáticas II de Madrid con un único pack descargable que reúne exámenes reales desde 1994 hasta 2025.</p>
<p>Incluye enunciados, soluciones y un PDF completo para estudiar de forma intensiva sin tener que navegar año por año.</p>
HTML)
            ->setStripeProductId($stripeProductId)
            ->setStripePriceId($stripePriceId)
            ->setPriceCents(1499)
            ->setCurrency('eur')
            ->setEnabled(true)
            ->setFiles([
                [
                    'key' => 'complete',
                    'label' => 'Pack completo: exámenes y soluciones',
                    'description' => '943 páginas.',
                    'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf',
                    'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf',
                ],
                [
                    'key' => 'enunciados',
                    'label' => 'Solo enunciados',
                    'description' => '194 páginas.',
                    'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-enunciados.pdf',
                    'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-enunciados.pdf',
                ],
                [
                    'key' => 'soluciones',
                    'label' => 'Solo soluciones',
                    'description' => '749 páginas.',
                    'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-soluciones.pdf',
                    'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-soluciones.pdf',
                ],
            ]);

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
