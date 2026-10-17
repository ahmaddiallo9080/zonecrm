<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function edit()
    {
        return view('settings.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_tagline' => ['nullable', 'string', 'max:150'],
            'company_phone' => ['nullable', 'string', 'max:60'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_address' => ['nullable', 'string', 'max:200'],
            'company_rccm' => ['nullable', 'string', 'max:60'],
            'company_nif' => ['nullable', 'string', 'max:60'],
            'currency' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['required', 'numeric', 'between:0,100'],
            'quote_validity_days' => ['required', 'integer', 'between:1,365'],
            'invoice_due_days' => ['required', 'integer', 'between:0,365'],
            'quote_footer' => ['nullable', 'string', 'max:1000'],
            'invoice_footer' => ['nullable', 'string', 'max:1000'],
        ]);

        Setting::put($data);

        return back()->with('success', 'Paramètres enregistrés.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$request->user()->id],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return back()->with('success', 'Profil mis à jour.');
    }
}
