<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReply;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactSubmissionController extends Controller
{
    public function index()
    {
        $submissions = ContactSubmission::query()
            ->latest()
            ->paginate(20);
        $unreadCount = ContactSubmission::query()->whereNull('read_at')->count();

        return view('content.admin.contact.index', compact('submissions', 'unreadCount'));
    }

    public function show(ContactSubmission $submission)
    {
        if ($submission->read_at === null) {
            $submission->update(['read_at' => now()]);
        }

        return view('content.admin.contact.show', compact('submission'));
    }

    public function reply(Request $request, ContactSubmission $submission)
    {
        $attributes = $request->validate([
            'reply' => ['required', 'string', 'max:10000'],
        ]);

        Mail::to($submission->email)->send(new ContactReply($submission, $attributes['reply']));

        $submission->update(['replied_at' => now()]);

        return redirect()->route('admin.contact.show', $submission)
            ->with('status', 'Votre réponse a été envoyée par e-mail.');
    }

    public function destroy(ContactSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.contact.index')
            ->with('status', 'Le message a été supprimé.');
    }
}
