<p>Bonjour {{ $submission->name }},</p>
<p>{!! nl2br(e($reply)) !!}</p>
<hr>
<p><strong>Votre demande :</strong> {{ $submission->subject }}</p>
<p>{!! nl2br(e($submission->message)) !!}</p>
