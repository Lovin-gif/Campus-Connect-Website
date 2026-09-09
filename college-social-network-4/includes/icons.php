<?php
/* Small inline-SVG icon set (Phosphor-style outline, hand-inlined since
   this project has no JS bundler / npm package install step available).
   Usage: icon('thumb') or icon('thumb', 'icon-lg') inside a PHP echo tag. */

function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'thumb' => '<path d="M7 22V11l5-8a3 3 0 0 1 3 3v5h5.5a2 2 0 0 1 1.94 2.5l-2 8A2 2 0 0 1 18.5 22H7Z"/><path d="M7 22H4a1 1 0 0 1-1-1V12a1 1 0 0 1 1-1h3"/>',
        'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.5 15.4 6.5"/><path d="M8.6 13.5 15.4 17.5"/>',
        'chat' => '<path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4 9 9 0 0 1-3.4-.7L3 21l1.8-5.4A8.4 8.4 0 1 1 21 11.5Z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'briefcase' => '<rect x="2.5" y="7" width="19" height="13" rx="2.5"/><path d="M8 7V5.5A2.5 2.5 0 0 1 10.5 3h3A2.5 2.5 0 0 1 16 5.5V7"/>',
        'megaphone' => '<path d="M3 10v4a1 1 0 0 0 1 1h2l3 5V4L6 9H4a1 1 0 0 0-1 1Z"/><path d="M11 6a7 7 0 0 1 0 12"/><path d="M14 8a4 4 0 0 1 0 8"/>',
        'network' => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="m8.2 7.3 6-1M8.4 8 12 15.5M15.7 7.4 12.3 15.5"/>',
        'shield' => '<path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
    ];

    if (!isset($paths[$name])) return '';
    return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
}
