<?php
namespace App\Controllers;

/**
 * Kök URL (/) isteğini karşılar; uygulama girişi panele yönlendirilir.
 */
class HomeController extends BaseController {
    public function index(): void {
        $this->redirect('/emlak/public/ana-pano');
    }
}
