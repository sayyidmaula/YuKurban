<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function landing(): Response
    {
        return Inertia::render('Public/Landing');
    }

    public function faq(): Response
    {
        return Inertia::render('Public/Faq');
    }

    public function ketentuan(): Response
    {
        return Inertia::render('Public/Ketentuan');
    }

    public function hubungi(): Response
    {
        return Inertia::render('Public/Hubungi');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactMessage::create([
            ...$validated,
            'status' => 'baru',
        ]);

        return back()->with('success', 'Pesan Anda telah berhasil dikirim. Tim kami akan segera menindaklanjutinya.');
    }
}
