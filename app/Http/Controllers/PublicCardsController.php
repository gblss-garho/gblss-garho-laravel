<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicCardsController extends PublicLookupController
{
    public function show()
    {
        return view('pages.id-cards', ['error' => null]);
    }

    public function download(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        if (! $student) {
            return view('pages.id-cards', ['error' => $error]);
        }

        return app(AdminIdCardController::class)->pdf($student);
    }
}
