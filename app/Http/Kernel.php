<?php

// app/Http/Kernel.php - Tambahkan ke $middlewareAliases

use Symfony\Component\HttpKernel\HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's middleware aliases.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        // ... middleware lainnya
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
}
