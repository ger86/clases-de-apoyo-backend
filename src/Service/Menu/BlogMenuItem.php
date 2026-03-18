<?php

namespace App\Service\Menu;

use App\Model\MenuLink;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogMenuItem implements MenuItemGeneratorInterface
{

    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    public static function getDefaultPriority(): int
    {
        return -10;
    }

    public function getMenuLinks(): array
    {
        $url = $this->router->generate('blog_index');
        $menuLink = new MenuLink('Blog', $url);
        return [$menuLink];
    }
}
