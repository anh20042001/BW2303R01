@extends('admin_layout')
@section('admin_content')
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Liệt kê thương hiệu sản phẩm
    </div>
    <div class="table-responsive">
        <?php
            $message = Session::get('message');
            if($message){
            echo '<span class="text-alert">'.$message.'</span>';
            Session::put('message',null);
            }
        ?>
        <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th>Tên danh mục</th>
            <th>Hiển thị</th>
            <th>Tùy chọn</th>
            <th style="width:30px;"></th>
          </tr>
        </thead>
        <tbody>
            @foreach($all_brand_product as $key =>$brand_pro)
          <tr>
            <td>{{ $brand_pro->brand_name }}</td>
            <td><span class="text-ellipsis">
            <?php
               if($brand_pro->brand_status==0){
                ?>
                <a href="{{URL::to('/unactive-brand-product/'.$brand_pro->brand_id)}}"><span class="fa-thumb-styling fa fa-thumbs-up"></span></a>
                <?php
                 }else{
                ?>
                 <a href="{{URL::to('/active-brand-product/'.$brand_pro->brand_id)}}"><span class="fa-thumb-styling fa fa-thumbs-down"></span></a>
                <?php
               }
              ?>
            </span></td>
            <td>
            <a href="{{URL::to('/edit-brand-product/'.$brand_pro->brand_id)}}" class="active styling-edit" ui-toggle-class="">
                <i class="fa fa-pencil-square-o text-success text-active"></i></a>
            <a onclick="return confirm('Bạn có chắc là muốn xóa danh mục này ko?')" href="{{URL::to('/delete-brand-product/'.$brand_pro->brand_id)}}" class="active styling-edit" ui-toggle-class="">
                <i class="fa fa-times text-danger text"></i>
            </a>
            </td>
        </tr>
        @endforeach
        </tbody>
        </table>
    </div>
    <div class="row">
  <form action="{{url('import-csv-brand')}}" method="POST" enctype="multipart/form-data">
          @csrf
          
        <input type="file" name="file" accept=".xlsx"><br>

       <input type="submit" value="Import file Excel" name="import_csv" class="btn btn-warning">
      </form>
<br>
    <!-----export data---->
       <form action="{{url('export-csv-brand')}}" method="POST">
          @csrf
       <input type="submit" value="Export file Excel" name="export_csv" class="btn btn-success">
      </form>
    </div>
  </div>
</div>
@endsection
