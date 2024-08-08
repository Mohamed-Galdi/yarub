<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCartItems();
        return view('home.cart', compact('cart'));
    }

    public function addToCart(Request $request)
    {
        $item = [
            'id' => $request->input('id'),
            'type' => $request->input('type'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'monthly_price' => $request->input('monthly_price'),
            'yearly_price' => $request->input('yearly_price'),
        ];

        $cart = $this->getCartItems();

        // Check if the item is already in the cart
        $existingItem = collect($cart)->where('id', $item['id'])->where('type', $item['type'])->first();
        if ($existingItem) {
            return response()->json(['error' => 'هذا العنصر موجود بالفعل في السلة'], 400);
        }

        $cart[] = $item;
        $this->storeCartItems($cart);

        return response()->json([
            'success' => 'تمت الإضافة إلى السلة',
            'count' => count($cart)
        ]);
    }

    public function updateCart(Request $request)
    {
        $itemId = $request->input('itemId');
        $type = $request->input('type');
        $newPlan = $request->input('newPlan');

        $cart = $this->getCartItems();
        $updatedCart = array_map(function ($item) use ($itemId, $type, $newPlan) {
            if ($item['id'] == $itemId && $item['type'] == $type) {
                $item['plan'] = $newPlan;
            }
            return $item;
        }, $cart);

        $this->storeCartItems($updatedCart);

        return response()->json([
            'success' => 'تم تغيير الخطة في السلة',
            'count' => count($updatedCart)
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $itemId = $request->input('itemId');
        $type = $request->input('type');

        $cart = $this->getCartItems();
        $updatedCart = array_filter($cart, function ($item) use ($itemId, $type) {
            return $item['id'] != $itemId || $item['type'] != $type;
        });

        $this->storeCartItems($updatedCart);

        return response()->json([
            'success' => 'تمت إزالة العنصر من السلة',
            'count' => count($updatedCart)
        ]);
    }

    private function getCartItems()
    {
        return Session::get('cart', []);
    }

    private function storeCartItems($cart)
    {
        Session::put('cart', $cart);
    }
}
