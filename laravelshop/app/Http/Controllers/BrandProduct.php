<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Exports\ExcelExportsBrand;
use App\Imports\ExcelImportsBrand;
use Excel;
use App\Models\BrandProductModel;

use Session;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
session_start();

class BrandProduct extends Controller
{
    public Function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            Redirect::to('admin.dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }
    public function add_brand_product(){

        return view('admin.add_brand_product');
    }

    public function all_brand_product(){

        $all_brand_product = DB::table('tbl_brand')->get();
        $manager_brand_product = view('admin.all_brand_product')->with('all_brand_product',$all_brand_product);
        return view('admin_layout')->with('admin.all_brand_product', $manager_brand_product);
    }

    public function save_brand_product(Request $request){

        $data = array();
        $data['brand_name'] = $request->brand_product_name;
    	$data['brand_desc'] = $request->brand_product_desc;
    	$data['brand_status'] = $request->brand_product_status;

    	DB::table('tbl_brand')->insert($data);
    	Session::put('message','Thêm danh mục thương hiệu thành công');
    	return Redirect::to('add-brand-product');
    }

    public function unactive_brand_product($brand_product_id){

        DB::table('tbl_brand')->where('brand_id',$brand_product_id)->update(['brand_status'=>1]);
        Session::put('message','Không kích hoạt danh mục thương hiệu thành công');
        return Redirect::to('all-brand-product');
    }

    public function active_brand_product($brand_product_id){

        DB::table('tbl_brand')->where('brand_id',$brand_product_id)->update(['brand_status'=>0]);
        Session::put('message','Kích hoạt danh mục thương hiệu thành công');
        return Redirect::to('all-brand-product');
    }

    public function edit_brand_product($brand_product_id){

        $edit_brand_product = DB::table('tbl_brand')->where('brand_id',$brand_product_id)->get();
        $manager_brand_product = view('admin.edit_brand_product')->with('edit_brand_product',$edit_brand_product);
        return view('admin_layout')->with('admin.edit_brand_product', $manager_brand_product);
    }

    public function update_brand_product(Request $request,$brand_product_id){

        $data = array();
        $data['brand_name'] = $request->brand_product_name;
        $data['brand_desc'] = $request->brand_product_desc;
        DB::table('tbl_brand_')->where('brand_id',$brand_product_id)->update($data);
        Session::put('message','cập nhật danh mục thương hiệu thành công');
        return Redirect::to('all-brand-product');
    }

    public function delete_brand_product($brand_product_id){

        DB::table('tbl_brand')->where('brand_id',$brand_product_id)->delete();
        Session::put('message','xóa danh mục thương hiệu thành công');
        return Redirect::to('all-brand-product');
    }
    //End Function Admin Page
    public function show_brand_home($brand_id){
        $cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get();
        $brand_by_id = DB::table('tbl_product')->join('tbl_brand','tbl_product.brand_id','=','tbl_brand.brand_id')->where('tbl_product.brand_id',$brand_id)->get();
        $brand_name =DB::table('tbl_brand')->where('tbl_brand.brand_id',$brand_id)->limit(1)->get();
        return view('pages.brand.show_brand')->with('category',$cate_product)->with('brand',$brand_product)->with('brand_by_id',$brand_by_id)->with('brand_name',$brand_name);

    }
    public function export_csv_brand(){
        return Excel::download(new ExcelExportsBrand, 'brand_product.xlsx');
    }
    public function import_csv_brand(Request $request){
        $path = $request->file('file')->getRealPath();
        Excel::import(new ExcelImportsBrand, $path);
        return back();
    }
}

