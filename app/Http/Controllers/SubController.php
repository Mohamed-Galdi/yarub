<?php

namespace App\Http\Controllers;

use App\Models\CourseSubscription;
use App\Models\LessonSubscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;


class SubController extends Controller
{
    public function subscribe(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'id' => 'required|string',
            'status' => 'required|string',
            'amount' => 'numeric',
            'message' => 'required|string',
        ]);

        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $paymentId = $validatedData['id'];
        $paymentStatus = $validatedData['status'];
        $paymentAmount = $validatedData['amount'];
        $paymentMessage = $validatedData['message'];
        $cart = session('cart', []);
        $coupon = session('coupon');
        $discount = session('discount', 0);

        DB::beginTransaction();

        try {
            $payment = new Payment();
            $payment->payment_id = $paymentId;
            $payment->payment_status = $paymentStatus;
            $payment->payment_message = $paymentMessage;
            $payment->original_amount = ($paymentAmount / 100) + $discount;
            if ($coupon) {
                $payment->coupon_used = $coupon['code'];
                $payment->coupon_reduction = $discount;
            }
            $payment->payment_amount = $paymentAmount / 100;
            $payment->save();

            if ($paymentStatus == 'paid' && $paymentMessage == 'APPROVED') {
                foreach ($cart as $item) {
                    if ($item['type'] == 'course') {
                        $courseSub = new CourseSubscription();
                        $courseSub->user_id = Auth::id();
                        $courseSub->course_id = $item['id'];
                        $courseSub->status = 'active';
                        $courseSub->payment_id = $payment->id;
                        $courseSub->save();
                    } else {
                        $lessonSub = new LessonSubscription();
                        $lessonSub->user_id = Auth::id();
                        $lessonSub->lesson_id = $item['id'];
                        $lessonSub->sub_plan = $item['plan'];
                        $lessonSub->status = 'active';
                        $lessonSub->payment_id = $payment->id;
                        $lessonSub->save();
                    }
                }
                session()->forget(['cart', 'coupon', 'discount']);
                DB::commit();
                return redirect()->route('thanks');
            } else {
                DB::rollBack();
                $msg = $this->getErrorMessage($paymentMessage);
                Alert::warning('لم تنجح عملية الدفع', $msg);
                return redirect()->route('cart.index');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            Log::error('Payment processing failed: ' . $e->getMessage());
            Alert::error('حدث خطأ أثناء معالجة الدفع', $e->getMessage());
            return redirect()->route('cart.index');
        }
    }

    private function getErrorMessage($message)
    {
        $errorMessages = [
            'INSUFFICIENT_FUNDS' => 'الرصيد غير كافي لإتمام العملية',
            '3-D Secure transaction attempt failed (Not Enrolled)' => 'فشلت المصادقة لإتمام العملية',
            'DECLINED' => 'تم رفض الدفع',
            'UNSPECIFIED_FAILUER' => 'حدث خطأ غير معروف ما أثناء الدفع',
        ];

        return $errorMessages[$message] ?? 'حدث خطأ غير معروف ما أثناء الدفع';
    }
}
