<?php

namespace App\Service\Menu;

use App\Model\MenuLink;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.menu_item_generator')]
interface MenuItemGeneratorInterface
{

    const MENU_ITEM_TAG = 'cda.menu_item';

    public static function getDefaultPriority(): int;

    /**
     * @return MenuLink[]
     */
    public function getMenuLinks(): array;
}
