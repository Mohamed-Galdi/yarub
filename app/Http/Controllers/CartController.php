<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCartItems();
        $totalBeforeDiscount = $this->calculateTotal($cart);
        $discount = session('discount', 0);
        $totalAfterDiscount = $totalBeforeDiscount - $discount;
        return view('home.cart', compact('cart', 'totalBeforeDiscount', 'totalAfterDiscount'));
    }

    // private function to update the discount on the session

    public function addToCart(Request $request)
    {
        $item = [
            'id' => $request->input('id'),
            'type' => $request->input('type'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'monthly_price' => $request->input('monthly_price'),
            'annual_price' => $request->input('annual_price'),
            'plan' => 'monthly',
        ];

        // check if the user is logged in and he is not a student
        if (Auth::check() && Auth::user()->role == 'admin') {
            return response()->json(['error' => 'لا يمكن الإضافة للسلة بحساب المشرف'], 401);
        }

        $cart = $this->getCartItems();

        // Check if the item is already in the cart
        $existingItem = collect($cart)->where('id', $item['id'])->where('type', $item['type'])->first();
        if ($existingItem) {
            return response()->json(['error' => 'هذا العنصر موجود بالفعل في السلة'], 400);
        }

        $cart[] = $item;
        $this->storeCartItems($cart);
        session(['discount' => 0]);
        session(['coupon' => []]);

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
        session(['discount' => 0]);
        session(['coupon' => []]);
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
        session(['discount' => 0]);
        session(['coupon' => []]);

        return response()->json([
            'success' => 'تمت إزالة العنصر من السلة',
            'count' => count($updatedCart)
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $couponCode = $request->input('coupon');
        $coupon = Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$coupon) {
            // update the session discount to 0
            session(['discount' => 0]);
            session(['coupon' => []]);
            Alert::error('قسيمة غير صالحة أو منتهية الصلاحية.');
            return redirect()->back();
        }

        $cart = $this->getCartItems();
        $totalBeforeDiscount = $this->calculateTotal($cart);
        $result = $this->calculateDiscount($coupon, $cart);
        $updatedCart = $result['updatedCart'];
        $totalAfterDiscount = $totalBeforeDiscount - $result['totalDiscount'];
        
        session(['cart' => $updatedCart]);
        session(['coupon' => $coupon->toArray(), 'discount' => $result['totalDiscount']]);

        Alert::success('تم تطبيق القسيمة بنجاح.');
        return redirect()->back();
    }

    private function calculateTotal($cart)
    {
        return collect($cart)->sum(function ($item) {
            if ($item['type'] === 'course') {
                return $item['price'];
            } elseif ($item['type'] === 'lesson') {
                if ($item['plan'] === 'monthly') {
                    return $item['monthly_price'];
                } else {
                    return $item['annual_price'];
                }
            }
        });
    }

    // private function calculateDiscount($coupon, $cart)
    // {
    //     $applicableItems = collect($cart)->filter(function ($item) use ($coupon) {
    //         if ($coupon->applicable_to === 'all') {
    //             return true;
    //         } elseif ($coupon->applicable_to === 'courses' && $item['type'] === 'course') {
    //             return true;
    //         } elseif ($coupon->applicable_to === 'lessons' && $item['type'] === 'lesson') {
    //             return true;
    //         } elseif ($coupon->applicable_to === 'specific') {
    //             return $coupon->courses->contains($item['id']) || $coupon->lessons->contains($item['id']);
    //         }
    //         return false;
    //     });

    //     $totalApplicable = $applicableItems->sum(function ($item) {
    //         if ($item['type'] === 'course') {
    //             return $item['price'];
    //         } elseif ($item['type'] === 'lesson') {
    //             if ($item['plan'] === 'monthly') {
    //                 return $item['monthly_price'];
    //             } else {
    //                 return $item['annual_price'];
    //             }
    //         }
    //     });

    //     if ($coupon->type === 'percentage') {
    //         return $totalApplicable * ($coupon->value / 100);
    //     } else {
    //         return min($coupon->value, $totalApplicable);
    //     }
    // }

    private function calculateDiscount($coupon, $cart)
    {
        $applicableItems = collect($cart)->map(function ($item) use ($coupon) {
            $item['applicable'] = false;

            if (
                $coupon->applicable_to === 'all' ||
                ($coupon->applicable_to === 'courses' && $item['type'] === 'course') ||
                ($coupon->applicable_to === 'lessons' && $item['type'] === 'lesson') ||
                ($coupon->applicable_to === 'specific' &&
                    ($coupon->courses->contains($item['id']) || $coupon->lessons->contains($item['id'])))
            ) {
                $item['applicable'] = true;
            }

            return $item;
        });

        $totalDiscount = 0;
        $updatedCart = $applicableItems->map(function ($item) use ($coupon, &$totalDiscount) {
            $originalPrice = $item['type'] === 'course' ? $item['price'] : ($item['plan'] === 'monthly' ? $item['monthly_price'] : $item['annual_price']);

            if ($item['applicable']) {
                if (
                    $coupon->type === 'percentage'
                ) {
                    $itemDiscount = $originalPrice * ($coupon->value / 100);
                } else {
                    $itemDiscount = min($coupon->value, $originalPrice);
                    $totalDiscount += $itemDiscount;
                    $coupon->value -= $itemDiscount; // Decrease the remaining fixed discount
                }

                $item['cost'] = $originalPrice - $itemDiscount;
                $totalDiscount += ($coupon->type === 'percentage' ? $itemDiscount : 0);
            } else {
                $item['cost'] = $originalPrice;
            }

            return $item;
        })->all();

        return [
            'totalDiscount' => $totalDiscount,
            'updatedCart' => $updatedCart
        ];
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
