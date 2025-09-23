<p>Hi {{ $waitlist->user->name }},</p>
<p>A seat just opened for <strong>{{ $event->title }}</strong>.</p>
<p>
  Date: {{ $event->starts_at->format('D, M j, Y g:ia') }}
  @if($event->ends_at) - {{ $event->ends_at->format('D, M j, Y g:ia') }} @endif
  Location: {{ $event->location }}
</p>
<p><a href="{{ $url }}">Book your seat now</a></p>
<p><strong>Deadline:</strong> {{ $waitlist->offer_expires_at->format('D, M j, Y g:ia') }}</p>
<p>If you haven't booked by then, the seat will automatically be offered to the next person on the waitlist.</p>