<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

use Zeus\Core\UI\MenuNode;
use Zeus\Core\UI\ScreenMetadata;

class UiRegistry
{
    private array $menus = [];
    private array $screens = [];

    public function registerMenu(MenuNode $menu): void
    {
        $this->menus[$menu->id] = $menu;
    }

    public function getMenus(): array
    {
        return array_values($this->menus);
    }

    public function registerScreen(ScreenMetadata $screen): void
    {
        $this->screens[$screen->id] = $screen;
    }

    public function getScreen(string $id): ?ScreenMetadata
    {
        return $this->screens[$id] ?? null;
    }
}
