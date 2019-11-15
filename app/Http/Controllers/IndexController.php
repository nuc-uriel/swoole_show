<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        var_dump($request->all());
    }
}
