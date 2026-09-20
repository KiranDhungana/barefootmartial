<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InstituteProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class InstituteProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user || (! $user->isSuperAdmin() && $user->role !== 'branch_admin')) {
                abort(403, 'Only academy admins can edit the institute profile.');
            }

            return $next($request);
        });
    }

    public function edit(): View
    {
        $profile = InstituteProfile::current();

        return view('erp.institute.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = InstituteProfile::current();

        $data = $request->validate([
            'legal_name' => 'nullable|string|max:255',
            'brand_line1' => 'nullable|string|max:120',
            'brand_line2' => 'nullable|string|max:120',
            'tagline' => 'nullable|string|max:255',
            'footer_tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:120',
            'district' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'country' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'pan' => 'nullable|string|max:64',
            'vat' => 'nullable|string|max:64',
            'fonepay_terminal' => 'nullable|string|max:120',
            'fonepay_address' => 'nullable|string|max:255',
            'authorized_signatory' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'qr_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'logo_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        unset($data['qr_file'], $data['logo_file']);

        if ($request->hasFile('qr_file')) {
            $data['qr_path'] = $this->storePublicImage($request->file('qr_file'), 'qr');
        }

        if ($request->hasFile('logo_file')) {
            $data['logo_path'] = $this->storePublicImage($request->file('logo_file'), 'logo');
        }

        $profile->fill($data);
        $profile->save();

        return redirect()
            ->route('erp.institute.edit')
            ->with('success', 'Institute profile updated. New location and contact details will appear on invoices, bills, and receipts.');
    }

    private function storePublicImage($file, string $prefix): string
    {
        $dir = public_path('images/institute');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = $prefix.'_'.time().'.'.$ext;
        $file->move($dir, $name);

        return 'images/institute/'.$name;
    }
}
