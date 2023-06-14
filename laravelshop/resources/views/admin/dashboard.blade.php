@extends('admin_layout')
@section('admin_content')
<div class="row">
    <h3 class="title_thongke text-center">Thống kê đơn hàng doanh số</h3> <br>
    <form autocomplete="off">
      @csrf
        <div class="col-md-2">
            <p>Từ ngày: <input type="text" id="datepicker" class="form-control">
            <input type="button" id="btn-dashboard-filter" class="btn btn-primary btn-sm " value="Lọc kết quả">
            </p>
        </div>
        <div class="col-md-2">
            <p>Đến ngày: <input type="text" class="form-control" id="datepicker2"></p>
        </div>
        <span class="col-md-1" style="margin-top:26px;">
            <p>Hoặc</p>
        </span>
        <div class="col-md-2">
            <p>Lọc theo:
            <select class="select-filter form-control">
                <option>--Chọn--</option>
                <option value="7ngay">7 ngày qua</option>
                <option value="thangtruoc">tháng trước</option>
                <option value="thangnay">tháng này</option>
                <option value="365ngayqua">365 ngày qua</option>
            </select>
            </p>
        </div>
    </form>
    <div class="col-md-12 ">
      <div id="chart" class=" text-white" style="height: 300px;background: white; margin-top:20px;"></div>
    </div>
  </div>
@endsection
