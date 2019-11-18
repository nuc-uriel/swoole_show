<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Symfony\Component\VarDumper\VarDumper;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        dump($request->all());
    }

    public function update(Request $request)
    {
        $path = $request->file('avatar')->store('avatars');
        return $path;
    }

    public function show(Request $request)
    {
        return response()->file(storage_path("/app/avatars/NtJi4fs3FuHhZdrLfOhPdIFN2NCvrJDLKvD1care.jpeg"));
    }

    public function download(Request $request)
    {
        return response()->download(storage_path("/app/avatars/NtJi4fs3FuHhZdrLfOhPdIFN2NCvrJDLKvD1care.jpeg"));
    }

}
