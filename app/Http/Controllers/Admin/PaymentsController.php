<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index()
    {
        // order from the latest to the oldest
        $payments = Payment::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.payments.payments', compact('payments'));
    }
        
}
