<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index()
    {
        $payments = Payment::with('user')->paginate(10);
        return view('admin.payments.payments', compact('payments'));
    }
        
}
