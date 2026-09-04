<?php
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function peso(float $amount): string
{
    return number_format($amount, 2);
}
function icon(string $name, string $class = ''): string
{
    $paths = [
        'home' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h4v-6h2v6h4a1 1 0 0 0 1-1v-9"/>',
        'chart' => '<line x1="4" y1="20" x2="4" y2="12"/><line x1="10" y1="20" x2="10" y2="4"/><line x1="16" y1="20" x2="16" y2="9"/><line x1="3" y1="20" x2="20" y2="20"/>',
        'archive' => '<rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.8h7.6a2 2 0 0 0 2-1.6L21 8H6"/>',
        'user-plus' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><line x1="18" y1="8" x2="18" y2="14"/><line x1="15" y1="11" x2="21" y2="11"/>',
        'logout' => '<path d="M13 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/><line x1="16" y1="12" x2="4" y2="12"/><polyline points="8 8 4 12 8 16"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><line x1="20" y1="20" x2="15.3" y2="15.3"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="3" x2="8" y2="7"/><line x1="16" y1="3" x2="16" y2="7"/>',
        'edit' => '<path d="M4 17.25V20h2.75L17.8 8.94l-2.75-2.75L4 17.25Z"/><path d="M14.5 5.5 16 4a1.5 1.5 0 0 1 2.12 0L19.5 5.38a1.5 1.5 0 0 1 0 2.12L18 9"/>',
        'trash' => '<path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'plus' => '<line x1="12" y1="4" x2="12" y2="20"/><line x1="4" y1="12" x2="20" y2="12"/>',
        'minus' => '<line x1="4" y1="12" x2="20" y2="12"/>',
        'x' => '<line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/>',
        'printer' => '<path d="M6 9V3h12v6"/><rect x="4" y="9" width="16" height="8" rx="1.5"/><path d="M6 14h12v7H6z"/>',
        'eye' => '<path d="M2 12s3.8-7 10-7 10 7 10 7-3.8 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 5.2A10.6 10.6 0 0 1 12 5c6.2 0 10 7 10 7a17.9 17.9 0 0 1-4 4.6M6.5 6.7C4 8.4 2 12 2 12s3.8 7 10 7c1.5 0 2.8-.3 4-.8"/><path d="M9.5 9.7a3 3 0 0 0 4.2 4.2"/>',
        'menu' => '<line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>',
        'alert-triangle' => '<path d="M10.6 4.3a1.6 1.6 0 0 1 2.8 0l8.4 14.6a1.6 1.6 0 0 1-1.4 2.4H3.6a1.6 1.6 0 0 1-1.4-2.4Z"/><line x1="12" y1="9.5" x2="12" y2="14"/><circle cx="12" cy="17.3" r="0.9" fill="currentColor" stroke="none"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><polyline points="8 12.5 11 15.5 16 9"/>',
        'undo' => '<path d="M3 11a9 9 0 1 1 2.6 6.3"/><polyline points="3 5 3 11 9 11"/>',
        'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
        'box' => '<path d="M3 8 12 4l9 4-9 4-9-4Z"/><path d="M3 8v9l9 4 9-4V8"/><line x1="12" y1="12" x2="12" y2="21"/>',
    ];

    $inner = $paths[$name] ?? '';
    $class = trim('icon ' . $class);

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true">' . $inner . '</svg>';
}
