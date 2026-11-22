<p><strong>Nom :</strong> {{ $contact['name'] }}</p>
<p><strong>E-mail :</strong> {{ $contact['email'] }}</p>
@if (!empty($contact['phone']))
  <p><strong>Téléphone :</strong> {{ $contact['phone'] }}</p>
@endif
<p><strong>Sujet :</strong> {{ $contact['subject'] }}</p>
<p><strong>Message :</strong></p>
<p>{!! nl2br(e($contact['message'])) !!}</p>
