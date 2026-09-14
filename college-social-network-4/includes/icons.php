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
        'edit' => '<path d="M17 3a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'trash' => '<path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13"/><path d="M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/>',
        'flag' => '<path d="M5 21V4"/><path d="M5 5h11l-2 4 2 4H5"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m21 21-4.3-4.3"/>',
        'bell' => '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'user-plus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M19 8v6M16 11h6"/>',
        'user-check' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="m16 12 2 2 4-4"/>',
        'camera' => '<path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1Z"/><circle cx="12" cy="14" r="3.5"/>',
        'file' => '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'star' => '<path d="m12 3 2.7 5.9 6.3.6-4.8 4.3 1.4 6.2L12 16.9 6.4 20l1.4-6.2L3 9.5l6.3-.6Z"/>',
        'ban' => '<circle cx="12" cy="12" r="9"/><path d="m5.5 5.5 13 13"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    ];

    if (!isset($paths[$name])) return '';
    return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
}
