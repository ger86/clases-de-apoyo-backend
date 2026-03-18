<?php

namespace App\TwigExtension;

use App\Service\BreadcrumbService;
use App\Service\Menu\MainMenuGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\{Environment, TwigFunction};
use Twig\Extension\AbstractExtension;

class PageElementsExtension extends AbstractExtension
{

    public function __construct(
        private MainMenuGenerator $mainMenuGenerator,
        private BreadcrumbService $breadcrumbService,
        private RequestStack $requestStack,
        private RouterInterface $router,
        private string $projectDir
    ) {
    }

    public function getName()
    {
        return 'pageElements';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('renderHeader', [$this, 'renderHeader'], [
                'is_safe' => ['html'],
                'needs_environment' => true
            ]),
            new TwigFunction('renderBreadcrumb', [$this, 'renderBreadcrumb'], [
                'is_safe' => ['html'],
                'needs_environment' => true
            ]),
            new TwigFunction('getCanonicalUrl', [$this, 'getCanonicalUrl'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction(
                'outputCss',
                [
                    $this,
                    'outputCss'
                ],
                [
                    'is_safe' => ['html']
                ]
            )
        ];
    }

    public function renderHeader(Environment $twig): string
    {
        $menuLinks = $this->mainMenuGenerator->getMenuLinks();
        return $twig->render('common/header/header.html.twig', [
            'menuLinks' => $menuLinks
        ]);
    }

    public function renderBreadcrumb(Environment $twig, mixed $element = null): string
    {
        return $twig->render('common/breadcrumb/breadcrumb.html.twig', [
            'breadcrumb' => $this->breadcrumbService->getBreadcrumb($element)
        ]);
    }

    public function outputCss(string $path): string|bool
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return file_get_contents($path);
        } else {
            return file_get_contents($this->projectDir . '/public' . $path);
        }
    }

    public function getCanonicalUrl(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $allParameters = $request->attributes->get('_route_params');
        return $this->router->generate($request->attributes->get('_route'), array_merge(
            $allParameters
        ), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
