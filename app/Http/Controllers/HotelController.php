<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index()
    {
        return view('client.index');
    }

    public function rooms()
    {
        return view('client.rooms');
    }

    public function restaurant()
    {   
        return view('client.restaurant');
    }

    public function about()
    {
        return view('client.about');
    }

    public function blog()
    {
        return view('client.blog');
    }

    public function contact()
    {
        return view('client.contact');
    }

    public function roomsSingle()
    {
        return view('client.rooms-single');
    }

    public function blogSingle()
    {
        return view('client.blog-single');
    }
    
    
}
