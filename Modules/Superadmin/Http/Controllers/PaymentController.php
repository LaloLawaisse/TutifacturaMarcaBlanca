<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use MercadoPago\SDK;
use MercadoPago\Payment;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        SDK::setAccessToken('APP_USR-4808059872337204-021920-cf6452878ea64b54d662efac74c0fe8c-432276693');
    
        $payment_data = $request->input('payment_data');
        $plan_id = $request->input('plan_id');
    
        // Verifica que el token no sea null
        if (empty($payment_data['token'])) {
            return response()->json(['status' => 'error', 'message' => 'Token is missing'], 400);
        }
    
        $payment = new Payment();
        $payment->transaction_amount = $payment_data['transaction_amount'] ?? 100;
        $payment->token = $payment_data['token'];
        $payment->description = "Pago para el plan: " . $plan_id;
        $payment->installments = $payment_data['installments'] ?? 1;
        $payment->payment_method_id = $payment_data['payment_method_id'] ?? null;
        $payment->payer = array(
            "email" => $payment_data['payer']['email'] ?? ''
        );
        
        try {
            $payment->save();
            return response()->json(['status' => 'success', 'payment_id' => $payment->id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function showPaymentPage(Request $request)
    {
        $planId = $request->query('planId');
        $preapprovalPlan = $request->query('preapprovalPlan');
    
        return view('mercadopago', compact('planId', 'preapprovalPlan'));
    }


}
