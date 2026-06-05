<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Registry;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Registry\UiRegistry;
use Zeus\Core\UI\MenuNode;
use Zeus\Core\UI\ScreenMetadata;

class UiRegistryTest extends TestCase
{
    public function test_it_registers_menus_with_children(): void
    {
        $registry = new UiRegistry();
        
        $usersMenu = new MenuNode(id: 'users', label: 'Utilisateurs', icon: 'user');
        $settingsMenu = new MenuNode(id: 'settings', label: 'Paramètres', icon: 'cog', children: [$usersMenu]);
        
        $registry->registerMenu($settingsMenu);
        
        $menus = $registry->getMenus();
        
        $this->assertCount(1, $menus);
        $this->assertEquals('settings', $menus[0]->id);
        $this->assertCount(1, $menus[0]->children);
        $this->assertEquals('users', $menus[0]->children[0]->id);
    }

    public function test_it_registers_screens(): void
    {
        $registry = new UiRegistry();
        
        $screen = new ScreenMetadata(id: 'users_grid', type: 'grid', entityCode: 'users');
        $registry->registerScreen($screen);
        
        $retrieved = $registry->getScreen('users_grid');
        
        $this->assertNotNull($retrieved);
        $this->assertEquals('users_grid', $retrieved->id);
        $this->assertEquals('grid', $retrieved->type);
        $this->assertEquals('users', $retrieved->entityCode);
    }

    public function test_it_returns_null_for_unknown_screen(): void
    {
        $registry = new UiRegistry();
        
        $this->assertNull($registry->getScreen('unknown'));
    }
}
