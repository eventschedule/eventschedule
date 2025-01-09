<?php

namespace App\Http\Controllers;

class InvoiceNinjaController extends Controller
{
    public function unlink()
    {
        $user = auth()->user();
        $user->invoiceninja_api_key = null;
        $user->invoiceninja_company_name = null;
        $user->save();

        return redirect()->back()->with('success', __('messages.invoiceninja_unlinked'));
    }
}