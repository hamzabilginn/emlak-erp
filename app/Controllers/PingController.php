<?php
namespace App\Controllers;

class PingController {
    public function index(): void {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(200);
        echo 'pong';
    }
}
