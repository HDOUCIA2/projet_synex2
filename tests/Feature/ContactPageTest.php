<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Mail\ContactReply;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_public_and_contains_the_form(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Nous sommes à votre écoute')
            ->assertSee('action="'.route('contact.store').'"', false);
    }

    public function test_contact_form_sends_a_message_to_the_configured_recipient(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Amina Issa',
            'email' => 'amina@example.org',
            'phone' => '+235 00 00 00',
            'subject' => 'Demande d’information',
            'message' => 'Merci de nous contacter.',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo(config('mail.contact_to'))
                && $mail->contact['email'] === 'amina@example.org';
        });

        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Amina Issa',
            'email' => 'amina@example.org',
            'subject' => 'Demande d’information',
            'message' => 'Merci de nous contacter.',
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_contact_form_reports_when_email_delivery_is_disabled(): void
    {
        config(['mail.default' => 'log']);

        $this->post(route('contact.store'), [
            'name' => 'Amina Issa',
            'email' => 'amina@example.org',
            'subject' => 'Question',
            'message' => 'Bonjour SYNEX.',
        ])->assertSessionHas('warning');

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'amina@example.org',
            'subject' => 'Question',
        ]);
    }

    public function test_administrator_can_read_reply_to_and_delete_a_contact_message(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = ContactSubmission::query()->create([
            'name' => 'Amina Issa',
            'email' => 'amina@example.org',
            'subject' => 'Question',
            'message' => 'Bonjour SYNEX.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contact.index'))
            ->assertOk()
            ->assertSee('Amina Issa');

        $this->get(route('admin.contact.show', $submission))
            ->assertOk()
            ->assertSee('Bonjour SYNEX.');

        $this->assertNotNull($submission->fresh()->read_at);

        $this->post(route('admin.contact.reply', $submission), [
            'reply' => 'Bonjour Amina, voici notre réponse.',
        ])->assertRedirect(route('admin.contact.show', $submission));

        Mail::assertSent(ContactReply::class, function (ContactReply $mail) {
            return $mail->hasTo('amina@example.org')
                && $mail->reply === 'Bonjour Amina, voici notre réponse.'
                && str_contains($mail->render(), $mail->reply);
        });
        $this->assertNotNull($submission->fresh()->replied_at);

        $this->delete(route('admin.contact.destroy', $submission))
            ->assertRedirect(route('admin.contact.index'));
        $this->assertDatabaseMissing('contact_submissions', ['id' => $submission->id]);
    }

    public function test_only_administrators_can_manage_contact_messages(): void
    {
        $submission = ContactSubmission::query()->create([
            'name' => 'Amina Issa',
            'email' => 'amina@example.org',
            'subject' => 'Question',
            'message' => 'Bonjour SYNEX.',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.contact.index'))
            ->assertForbidden();

        auth()->logout();
        $this->get(route('admin.contact.show', $submission))
            ->assertRedirect(route('auth-login-basic'));
    }
}
