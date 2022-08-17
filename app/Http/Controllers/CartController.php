<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Inventory;
use Auth;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $cart = Cart::where('inventory_id', $request->id)
                        ->where('status', 'in')
                        ->where('employee_id', Auth::user()->id)
                        ->first();
        if($cart){
            $cart->quantity += 1;
            $cart->save();

            $inventory = Inventory::find($request->id);
            $inventory->quantity -= 1;
            $inventory->save();
        }else{
            $add = Cart::create([
                'employee_id' => Auth::user()->id,
                'inventory_id' => $request->id,
                'quantity' => 1,
                'status' => 'in',
            ]);
    
            $inventory = Inventory::find($request->id);
            $inventory->quantity -= 1;
            $inventory->save();
        }
        
       return redirect()->back()->with('success', 'Added to Cart');
    }

    public function checkout(Request $request){
        $carts = Cart::where('employee_id', Auth::user()->id)
                        ->where('status', 'in')
                        ->get();
        $sub_price = 0;
        $total_price = 0;
        
        foreach($carts as $cart){
            $inventory = Inventory::find($cart->inventory_id);
            $sub_price += $cart->quantity * $inventory->price;
        }
        $tax = 5;
        $total_price += $sub_price *(5/100);
        return view('checkout', ['carts' => $carts,
                                 'tax' => $tax,
                                 'sub_price' => $sub_price,
                                 'total_price' => $total_price]);
    }
}
