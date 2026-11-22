<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessage;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactPageController extends Controller
{
    public function index()
    {
        return view('content.pages.contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $submission = ContactSubmission::query()->create($validated);

        if (config('mail.default') === 'log') {
            Log::warning('Contact message notification was not sent because the mailer is configured to log.', [
                'contact_submission_id' => $submission->id,
            ]);

            return back()->with('warning', 'Votre message a été enregistré dans l’administration, mais l’envoi des e-mails n’est pas activé.');
        }

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactMessage($validated));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Contact message notification could not be sent.', [
                'contact_submission_id' => $submission->id,
                'exception' => $exception,
            ]);

            return back()->with('warning', 'Votre message a été enregistré, mais la notification par e-mail n’a pas pu être envoyée.');
        }

        return back()->with('success', 'Votre message a bien été enregistré. Nous vous répondrons dans les meilleurs délais.');
    }
}
