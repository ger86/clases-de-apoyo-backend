<?php

namespace App\Controller;

use App\Entity\ProductPurchase;
use App\Repository\ProductPurchaseRepository;
use App\Repository\ProductRepository;
use App\Service\Security;
use App\Service\Stripe\StripeCreateProductCheckoutSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ProductController extends AbstractController
{
    public function show(string $slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findEnabledBySlug($slug);
        if ($product === null) {
            throw $this->createNotFoundException('No existe ese producto');
        }

        return $this->render('views/products/show.html.twig', [
            'product' => $product,
        ]);
    }

    public function checkout(
        string $slug,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        StripeCreateProductCheckoutSession $createCheckoutSession
    ): RedirectResponse {
        $product = $productRepository->findEnabledBySlug($slug);
        if ($product === null) {
            throw $this->createNotFoundException('No existe ese producto');
        }

        if (!$this->isCsrfTokenValid(\sprintf('product_checkout_%d', $product->getId()), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('La sesión de pago no es válida.');
        }

        $purchase = new ProductPurchase($product);
        $purchase
            ->setUser($security->getUser())
            ->setAmountTotal($product->getPriceCents())
            ->setCurrency($product->getCurrency());

        $entityManager->persist($purchase);
        $entityManager->flush();

        $session = ($createCheckoutSession)($purchase);
        $purchase->setStripeCheckoutSessionId($session->id);
        $entityManager->flush();

        return new RedirectResponse((string) $session->url);
    }

    public function success(string $token, ProductPurchaseRepository $purchaseRepository): Response
    {
        $purchase = $purchaseRepository->findOneBy(['downloadToken' => $token]);
        if ($purchase === null) {
            throw $this->createNotFoundException('No existe esa compra');
        }

        return $this->render('views/products/success.html.twig', [
            'purchase' => $purchase,
            'product' => $purchase->getProduct(),
        ]);
    }

    public function download(
        string $token,
        string $fileKey,
        ProductPurchaseRepository $purchaseRepository,
        string $projectDir
    ): BinaryFileResponse {
        $purchase = $purchaseRepository->findPaidByToken($token);
        if ($purchase === null) {
            throw $this->createAccessDeniedException('No puedes descargar este producto.');
        }

        $productFile = $purchase->getProduct()->getFileByKey($fileKey);
        if ($productFile === null) {
            throw $this->createNotFoundException('No existe ese archivo.');
        }

        $path = $projectDir . '/' . ltrim($productFile['path'], '/');
        if (!is_file($path)) {
            throw $this->createNotFoundException('El archivo todavía no está disponible.');
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $productFile['filename']
        );

        return $response;
    }
}
