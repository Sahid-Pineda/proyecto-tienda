<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function portal_get_page_title(string $title): string {
    return trim($title) !== '' ? $title . ' · ' . APP_NAME : APP_NAME;
}

function portal_nav_items(string $activePage = ''): array {
    return [
        ['label' => 'Inicio', 'url' => 'index.php', 'slug' => 'index'],
        ['label' => 'Productos', 'url' => 'productos.php', 'slug' => 'productos'],
        ['label' => 'Categorías', 'url' => 'categorias.php', 'slug' => 'categorias'],
        ['label' => 'Proveedores', 'url' => 'proveedores.php', 'slug' => 'proveedores'],
        ['label' => 'Usuarios', 'url' => 'usuarios.php', 'slug' => 'usuarios'],
        ['label' => 'Compras', 'url' => 'compras.php', 'slug' => 'compras'],
        ['label' => 'Ventas', 'url' => 'ventas.php', 'slug' => 'ventas'],
    ];
}

function portal_render_theme(): void {
    echo <<<HTML
<style>
    :root {
        color-scheme: light;
        --primary: #1d4ed8;
        --primary-soft: #dbeafe;
        --accent: #0f766e;
        --text: #0f172a;
        --muted: #64748b;
        --surface: #ffffff;
        --border: #e2e8f0;
    }

    body {
        font-family: "Segoe UI", Roboto, Arial, sans-serif;
        background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%);
        color: var(--text);
    }

    .navbar-brand {
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .navbar-dark {
        background: linear-gradient(135deg, var(--primary), #2563eb 85%);
    }

    .card {
        border: 1px solid var(--border);
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .table thead {
        background-color: #eff6ff;
        color: #1e3a8a;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-outline-light:hover {
        background-color: rgba(255,255,255,0.16);
    }

    .page-title {
        font-size: 1.7rem;
        font-weight: 700;
        color: var(--text);
    }

    .page-subtitle {
        color: var(--muted);
        margin-top: 0.3rem;
    }

    .footer-card {
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(6px);
    }
</style>
HTML;
}
