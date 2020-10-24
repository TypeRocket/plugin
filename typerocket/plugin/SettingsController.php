<?php
namespace TypeRocketPlugin;

use TypeRocket\Controllers\Controller;

final class SettingsController extends Controller
{
    public function typerocket()
    {
        return tr_view(__DIR__ . '/../../admin.php');
    }
}