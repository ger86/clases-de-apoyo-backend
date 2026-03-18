<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Book;
use App\Model\Dto\StripeCreatePaymentIntentDto;
use App\Service\Stripe\StripeUtils;
use App\Service\Stripe\API\StripeCreatePaymentIntent;
use App\Repository\BookRepository;
use LogicException;

class BookShopController extends AbstractController
{

    public function book(
        string $slug,
        BookRepository $bookRepository
    ): Response {
        throw new LogicException('Not work');
        // $form = $this->createFormBuilder()
        //     ->setAction($this->generateUrl('shop_book_payment', ['slug' => $slug]))
        //     ->add('email', EmailType::class, [
        //         'label' => 'Introduce el email al que quieres que te enviemos el libro'
        //     ])
        //     ->add('submit', SubmitType::class, ['label' => 'Siguiente paso'])
        //     ->getForm();
        // $book = $bookRepository->findOneBySlug($slug);
        // return $this->render('views/shop/book/book.html.twig', [
        //     'book' => $book,
        //     'form' => $form->createView()
        // ]);
    }

    public function bookPayment(
        Request $request,
        string $slug,
        BookRepository $bookRepository,
        // StripeCreatePaymentIntent $stripeCreatePaymentIntent,
        // StripeUtils $stripeUtils
    ): Response {

        throw new LogicException('Not work');
        // $form = $request->get('form', false);
        // if ($form === false) {
        //     return $this->redirectToRoute('shop_book', ['slug' => $slug]);
        // }
        // $email = $form['email'] ?? null;
        // if ($email === null) {
        //     return $this->redirectToRoute('shop_book', ['slug' => $slug]);
        // }
        // $book = $bookRepository->findOneBy(['slug' => $slug]);
        // if ($book === null) {
        //     throw $this->createNotFoundException('Ese libro no existe');
        // }
        // $dto = new StripeCreatePaymentIntentDto(
        //     $stripeUtils->convertToStringAmount($book->getPrice()),
        //     $email,
        //     [
        //         'data' => json_encode([
        //             'class' => Book::class,
        //             'id' => $book->getId(),
        //             'email' => $email
        //         ])
        //     ]
        // );
        // $paymentIntent = $stripeCreatePaymentIntent($dto);

        // return $this->render('views/shop/book_payment/book_payment.html.twig', [
        //     'book' => $book,
        //     'paymentIntent' => $paymentIntent,
        //     'email' => $email
        // ]);
    }

    public function bookPaymentSuccess(
        string $slug,
        BookRepository $bookRepository
    ): Response {
        throw new LogicException('Not work');
        // $book = $bookRepository->findOneBy(['slug' => $slug]);
        // return $this->render('views/shop/book/book_payment_success.html.twig', [
        //     'book' => $book
        // ]);
    }
}
