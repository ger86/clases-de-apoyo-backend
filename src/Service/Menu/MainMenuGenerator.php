<?php

namespace App\Service\Menu;

use App\Model\MenuLink;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class MainMenuGenerator
{
    /** @var MenuItemGeneratorInterface[] $menuItems */
    private $menuItemGenerators = [];

    public function __construct(
        #[AutowireIterator('app.menu_item_generator')]
        iterable $menuItemGenerators
    ) {
        $this->menuItemGenerators = iterator_to_array($menuItemGenerators);
    }

    /**
     * @return MenuLink[]
     */
    public function getMenuLinks(): array
    {
        $dev = [];
        foreach ($this->menuItemGenerators as $menuItemGenerator) {
            $dev = array_merge($dev, $menuItemGenerator->getMenuLinks());
        }
        return $dev;
    }
}
