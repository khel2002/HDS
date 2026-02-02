<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Use a view composer to dynamically load menu based on user role
        View::composer('*', function ($view) {
            $menuData = $this->getMenuByRole();
            $view->with('menuData', [$menuData]);
        });
    }

    /**
     * Get menu data based on authenticated user's role
     */
    private function getMenuByRole()
    {
        // Default menu for guests
        $menuFile = 'verticalMenu.json';

        if (Auth::check()) {
            $user = Auth::user();


            $roleMenuMap = [
                4 => 'super-admin.json',
                2 => 'staff.json',
                3 => 'admin.json',
                1 => 'verticalMenu.json',
            ];

            // Get the role_id from the user
            $roleId = $user->role_id;
            $menuFile = $roleMenuMap[$roleId] ?? 'verticalMenu.json';
        }

        $menuPath = base_path("resources/menu/{$menuFile}");

        if (file_exists($menuPath)) {
            $menuJson = file_get_contents($menuPath);
            return json_decode($menuJson);
        }

        // Fallback to default menu if file doesn't exist
        $defaultMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        return json_decode($defaultMenuJson);
    }
}
