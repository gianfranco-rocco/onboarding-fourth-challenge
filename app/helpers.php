<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

function vite_assets(): HtmlString
{
    $devServerIsRunning = false;
    
    if (app()->environment('local')) {
        try {
            Http::get("http://localhost:3000");
            $devServerIsRunning = true;
        } catch (Exception) {
        }
    }
    
    if ($devServerIsRunning) {
        return new HtmlString(<<<HTML
            <script type="module" src="http://localhost:3000/@vite/client"></script>
            <script type="module" src="http://localhost:3000/resources/js/app.js"></script>
        HTML);
    }
    
    $manifest = json_decode(file_get_contents(
        public_path('build/manifest.json')
    ), true);
    
    $jsModule = $manifest['resources/js/app.js']['file'] ?? '';
    $css = $manifest['resources/js/app.js']['css'][0] ?? '';

    $html = '';

    if ($jsModule) {
        $html = new HtmlString(<<<HTML
            <script type="module" src="/build/{$jsModule}"></script>
        HTML);
    }

    if ($css) {
        $html .= new HtmlString(<<<HTML
            <link rel="stylesheet" href="/build/{$css}">
        HTML);
    }

    return new HtmlString($html);
}