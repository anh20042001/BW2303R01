<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipping;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Statistic;
use App\Models\Coupon;
use Carbon\Carbon;
use PDF;


class OrderController extends Controller
{
	public function update_qty(Request $request){
		$data = $request->all();
		$order_details = OrderDetails::where('product_id',$data['order_product_id'])->where('order_code',$data['order_code'])->first();
		$order_details->product_sales_quantity = $data['order_qty'];
		$order_details->save();
	}
	public function update_order_qty(Request $request){
		//update order
		$data = $request->all();
		$order = Order::find($data['order_id']);
		$order->order_status = $data['order_status'];
		$order->save();

		//order_date
		$order_date = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
		$statistic = Statistic::where('order_date',$order_date)->get();

		if($statistic){
			$statistic_count = $statistic->count();
		}else{
			$statistic_count= 0;
		}
		if($order->order_status==2){

			$total_order = 0;
			$sales = 0;
			$profit = 0;
			$quantity = 0;


			foreach($data['order_product_id'] as $key => $product_id){
				$product = Product::find($product_id);
				$product_quantity = $product->product_quantity;
				$product_sold = $product->product_sold;

				
				$product_price = $product->product_price;
				$product_cost = $product->price_cost;
				$now = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();

				foreach($data['quantity'] as $key2 => $qty){
						if($key==$key2){
								$pro_remain = $product_quantity - $qty;
								$product->product_quantity = $pro_remain;
								$product->product_sold = $product_sold + $qty;
								$product->save();
								//update doanh thu
								$quantity += $qty;
								$total_order += 1;
								$sales += $product_price*$qty;
								$profit = $sales-($product_cost*$qty);
						}
				}

			}
			// update doanh so DB
			if($statistic_count>0){
				$statistic_update = Statistic::where('order_date',$order_date)->first();
				$statistic_update->sales = $statistic_update->sales + $sales;
				$statistic_update->profit = $statistic_update->profit + $profit;
				$statistic_update->quantity = $statistic_update->quantity + $quantity;
				$statistic_update->total_order = $statistic_update->total_order + $total_order;
				
				$statistic_update->save();
			} else{
				$statistic_new = new Statistic();
				$statistic_new->order_date = $order_date;
				$statistic_new->sales = $sales;
				$statistic_new->profit = $profit;
				$statistic_new->quantity = $quantity;
				$statistic_new->total_order = $total_order;
				$statistic_new->order_date = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
				$statistic_new->save();
			}

		}

	}
	// public function update_order_qty(Request $request){
	// 	//update order
	// 	$data = $request->all();
	// 	$order = Order::find($data['order_id']);
	// 	$order->order_status = $data['order_status'];
	// 	$order->save();
	// 	if($order->order_status==2){
	// 		foreach($data['order_product_id'] as $key => $product_id){				
	// 			$product = Product::find($product_id);
	// 			$product_quantity = $product->product_quantity;
	// 			$product_sold = $product->product_sold;
	// 			foreach($data['quantity'] as $key2 => $qty){
	// 					if($key==$key2){
	// 						$pro_remain = $product_quantity - $qty;
	// 						$product->product_quantity = $pro_remain;
	// 						$product->product_sold = $product_sold + $qty;
	// 						$product->save();
	// 					}
	// 			}
	// 		}
	// 	}elseif($order->order_status!=2 && $order->order_status!=3){
	// 		foreach($data['order_product_id'] as $key => $product_id){				
	// 			$product = Product::find($product_id);
	// 			$product_quantity = $product->product_quantity;
	// 			$product_sold = $product->product_sold;
	// 			foreach($data['quantity'] as $key2 => $qty){
	// 				if($key==$key2){
	// 						$pro_remain = $product_quantity + $qty;
	// 						$product->product_quantity = $pro_remain;
	// 						$product->product_sold = $product_sold - $qty;
	// 						$product->save();
	// 				}
	// 			}
	// 		}
	// 	}
	// }
	public function print_order($checkout_code){
		$pdf = \App::make('dompdf.wrapper');
		$pdf->loadHTML($this->print_order_convert($checkout_code));
		
		return $pdf->stream();
	}
	public function print_order_convert($checkout_code){
		$order_details = OrderDetails::where('order_code',$checkout_code)->get();
		$order = Order::where('order_code',$checkout_code)->get();
		foreach($order as $key => $ord){
			$customer_id = $ord->customer_id;
			$shipping_id = $ord->shipping_id;
		}
		$customer = Customer::where('customer_id',$customer_id)->first();
		$shipping = Shipping::where('shipping_id',$shipping_id)->first();

		$order_details_product = OrderDetails::where('order_code', $checkout_code)->get();
		foreach($order_details_product as $key => $order_d){

			$product_coupon = $order_d->product_coupon;
		}
		if($product_coupon != 'no'){
			$coupon = Coupon::where('coupon_code',$product_coupon)->first();

			$coupon_condition = $coupon->coupon_condition;
			$coupon_number = $coupon->coupon_number;

			if($coupon_condition==1){
				$coupon_echo = $coupon_number.'%';
			}elseif($coupon_condition==2){
				$coupon_echo = number_format($coupon_number,0,',','.').'đ';
			}
		}else{
			$coupon_condition = 2;
			$coupon_number = 0;

			$coupon_echo = '0';
		
		}

		$output = '';

		$output.='<style>body{
			font-family: DejaVu Sans;
		}
		.table-styling{
			border:1px solid #000;
			width:100%;
		}
		.table-styling th{
			border:1px solid #000;
			text-align:center;
		}
		.table-styling tbody tr td{
			border:1px solid #000;
		}
		.table-styling tr{
			text-align:center;
		}
		</style>
		<h1>Cửa hàng E-Shop</h1>
		<h5><center>Địa chỉ: Tầng 1, tòa nhà Landmark 81, Thanh Xuân, Hà Nội</center></h5>
		<h5><center>Email: shoplaravel@gmail.com/ Hotline:0987654321</center></h5>
		<h3><center>Đơn hàng</center></h3>
		<b>Người đặt hàng</b>
		<table class="table-styling">
				<thead>
					<tr>
						<th>Tên khách đặt</th>
						<th>Số điện thoại</th>
						<th>Email</th>
					</tr>
				</thead>
				<tbody>';
				
		$output.='		
					<tr>
						<td>'.$customer->customer_name.'</td>
						<td>'.$customer->customer_phone.'</td>
						<td>'.$customer->customer_email.'</td>
						
					</tr>';
				

		$output.='				
				</tbody>
			
		</table><br>

		<b>Ship hàng tới</b>
			<table class="table-styling">
				<thead>
					<tr>
						<th>Tên người nhận</th>
						<th>Địa chỉ</th>
						<th>Số điện thoại</th>
						<th>Email</th>
						<th>Ghi chú</th>
					</tr>
				</thead>
				<tbody>';
				
		$output.='		
					<tr>
						<td>'.$shipping->shipping_name.'</td>
						<td>'.$shipping->shipping_address.'</td>
						<td>'.$shipping->shipping_phone.'</td>
						<td>'.$shipping->shipping_email.'</td>
						<td>'.$shipping->shipping_notes.'</td>
						
					</tr>';
				

		$output.='				
				</tbody>
			
		</table><br>

		<b>Đơn hàng đặt</b>
			<table class="table-styling">
				<thead>
					<tr>
						<th>Tên sản phẩm</th>
						<th>Mã giảm giá</th>
						<th>Số lượng</th>
						<th>Giá sản phẩm</th>
						<th>Thành tiền</th>
					</tr>
				</thead>
				<tbody>';
			
				$total = 0;

				foreach($order_details_product as $key => $product){

					$subtotal = $product->product_price*$product->product_sales_quantity;
					$total+=$subtotal;

					if($product->product_coupon!='no'){
						$product_coupon = $product->product_coupon;
					}else{
						$product_coupon = 'không mã';
					}		

		$output.='		
					<tr>
						<td>'.$product->product_name.'</td>
						<td>'.$product_coupon.'</td>
						<td>'.$product->product_sales_quantity.'</td>
						<td>'.number_format($product->product_price,0,',','.').'đ'.'</td>
						<td>'.number_format($subtotal,0,',','.').'đ'.'</td>
						
					</tr>';
				}

				if($coupon_condition==1){
					$total_after_coupon = ($total*$coupon_number)/100;
	                $total_coupon = $total - $total_after_coupon;
				}else{
                  	$total_coupon = $total - $coupon_number;
				}
		$output.= '<tr>
				<td colspan="2">
					<p>Tổng giảm: '.$coupon_echo.'</p>
					<p>Thanh toán : '.number_format($total_coupon,0,',','.').'đ'.'</p>
				</td>
		</tr>';
		$output.='				
		</tbody>
		</table><br/>
		<b>Ghi chú:......................................................................................................</b><br/><br/>
		<b>Ký tên</b>
		<table>
			<thead>
				<tr>
					<th width="200px">Người lập phiếu</th>
					<th width="800px">Người nhận</th>
				</tr>
			</thead>
			<tbody>';	
	    $output.='				
			</tbody>		
		</table>
		';
		return $output;

	}
    public function manage_order(){
    	$order = Order::orderby('created_at','DESC')->paginate(5);
    	return view('admin.manage_order')->with(compact('order'));
    }
    public function view_order($order_code){
		$order_details = OrderDetails::with('product')->where('order_code',$order_code)->get();
		$order = Order::where('order_code',$order_code)->get();
		foreach($order as $key => $ord){
			$customer_id = $ord->customer_id;
			$shipping_id = $ord->shipping_id;
			$order_status = $ord->order_status;
		}
		$customer = Customer::where('customer_id',$customer_id)->first();
		$shipping = Shipping::where('shipping_id',$shipping_id)->first();

		$order_details_product = OrderDetails::with('product')->where('order_code', $order_code)->get();

		foreach($order_details_product as $key => $order_d){

			$product_coupon = $order_d->product_coupon;
		}
		if($product_coupon != 'no'){
			$coupon = Coupon::where('coupon_code',$product_coupon)->first();
			$coupon_condition = $coupon->coupon_condition;
			$coupon_number = $coupon->coupon_number;
		}else{
			$coupon_condition = 2;
			$coupon_number = 0;
		}	
		return view('admin.view_order')->with(compact('order_details','customer','shipping','order_details','coupon_condition','coupon_number','order','order_status',));

	}
}
