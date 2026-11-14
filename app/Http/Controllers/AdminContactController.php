<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminContactController extends Controller
{
    public function index()
    {
        return view('admin.contact-messages', [
            'messages' => DB::table('contact_messages')->orderByDesc('created_at')->limit(200)->get(),
        ]);
    }

    public function destroy(string $id)
    {
        DB::table('contact_messages')->where('id', $id)->delete();

        return redirect(route('admin.contact.index', [], false))->with('ok', 'Message delete ho gaya.');
    }
}
