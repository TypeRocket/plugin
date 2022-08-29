<?php
namespace TypeRocketPlugin;

use TypeRocket\Controllers\Controller;
use TypeRocket\Template\TemplateEngine;

final class SettingsController extends Controller
{
    public function typerocket()
    {
        return tr_view(__DIR__ . '/../../admin.php')->setEngine(TemplateEngine::class);
    }
}