<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\QuoteRequest;

class DashboardController extends AdminController
{
    public function index(): string
    {
        return $this->render('dashboard', [
            'stats' => [
                'products'   => (int) DB::value('SELECT COUNT(*) FROM products'),
                'active'     => (int) DB::value('SELECT COUNT(*) FROM products WHERE is_active = 1'),
                'categories' => (int) DB::value('SELECT COUNT(*) FROM categories'),
                'pages'      => (int) DB::value('SELECT COUNT(*) FROM pages'),
                'quotes_new' => QuoteRequest::countNew(),
            ],
            'latestQuotes' => array_slice(QuoteRequest::all(), 0, 8),
        ]);
    }
}
