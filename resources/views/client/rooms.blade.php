@extends('client.layouts.master')

@section('title', 'Danh Sách Phòng')

@section('content')
    <div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
          <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
          	<div class="text">
	            <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span> <span>Phòng</span></p>
	            <h1 class="mb-4 bread">Danh Sách Phòng</h1>
            </div>
          </div>
        </div>
      </div>
    </div>


    <section class="ftco-section bg-light">
    	<div class="container">
    		<div class="row">
	        <div class="col-lg-9">
		    		<div class="row">
                    @if($rooms->count() > 0)
                        @foreach($rooms as $room)
                        <div class="col-sm col-md-6 col-lg-4 ftco-animate">
                            <div class="room">
                                <a href="{{ route('rooms-single', $room->id) }}" class="img d-flex justify-content-center align-items-center" style="background-image: url(client/images/room-{{ $loop->iteration % 6 + 1 }}.jpg);">
                                    <div class="icon d-flex justify-content-center align-items-center">
                                        <span class="icon-search2"></span>
                                    </div>
                                </a>
                                <div class="text p-3 text-center">
                                    <h3 class="mb-3"><a href="{{ route('rooms-single', $room->id) }}">{{ $room->roomType->name }}</a></h3>
                                    <p><span class="price mr-2">{{ number_format($room->price) }}đ</span> <span class="per">mỗi đêm</span></p>
                                    <ul class="list">
                                        <li><span>Sức chứa:</span> {{ $room->capacity }} Người</li>
                                        <li><span>Phòng số:</span> {{ $room->room_number }}</li>
                                        <li><span>Trạng thái:</span> {{ $room->status == 'available' ? 'Còn trống' : 'Đã đặt' }}</li>
                                    </ul>
                                    <hr>
                                    <p class="pt-1">
                                        <a href="{{ route('rooms-single', $room->id) }}" class="btn-custom">Chi tiết <span class="icon-long-arrow-right"></span></a>
                                        @if($room->status == 'available')
                                        <a href="{{ route('booking') }}" class="btn-custom ml-2">Đặt ngay <span class="icon-long-arrow-right"></span></a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center py-5">
                            <h3>Không có phòng nào</h3>
                            <p>Vui lòng thử lại sau</p>
                        </div>
                    @endif
		    		</div>
		    	</div>
		    	<div class="col-lg-3 sidebar">
	      		<div class="sidebar-wrap bg-light ftco-animate">
	      			<h3 class="heading mb-4">Tìm phòng</h3>
	      			<form action="{{ route('rooms') }}" method="GET">
	      				<div class="fields">
		              <div class="form-group">
		                <input type="text" name="keyword" class="form-control" placeholder="Từ khóa">
		              </div>
		              <div class="form-group">
		                <div class="select-wrap one-third">
	                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
	                    <select name="capacity" id="" class="form-control">
	                    	<option value="">Số người</option>
	                    	<option value="1">1 người</option>
	                    	<option value="2">2 người</option>
	                    	<option value="3">3 người</option>
	                    	<option value="4">4 người</option>
	                    	<option value="5">5 người</option>
	                    	<option value="6">6 người trở lên</option>
	                    </select>
	                  </div>
		              </div>
		              <div class="form-group">
		                <div class="select-wrap one-third">
	                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
	                    <select name="type" id="" class="form-control">
	                    	<option value="">Loại phòng</option>
                            @foreach(\App\Models\RoomType::all() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
	                    </select>
	                  </div>
		              </div>
		              <div class="form-group">
		              	<div class="range-slider">
		              		<span>
							    <input type="number" name="min_price" value="0" min="0" max="5000000"/>	-
							    <input type="number" name="max_price" value="5000000" min="0" max="5000000"/>
							</span>
                            <label>Khoảng giá (đ)</label>
						</div>
		              </div>
		              <div class="form-group">
		                <input type="submit" value="Tìm kiếm" class="btn btn-primary py-3 px-5">
		              </div>
		            </div>
	            </form>
	      		</div>
	        </div>
		    </div>
    	</div>
    </section>
@endsection