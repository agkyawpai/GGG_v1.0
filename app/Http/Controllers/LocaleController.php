<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['locale' => $request->session()->get('locale', config('app.locale'))]);
    }

    public function update(Request $request)
    {
        $request->validate(['locale' => 'required|in:en,my']);

        $request->session()->put('locale', $request->locale);

        return back();
    }
}
