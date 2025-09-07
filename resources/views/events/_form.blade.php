@php($ev = $event ?? null)

<p><label>Title
  <input name="title" value="{{ old('title', $ev->title ?? '') }}" maxlength="100" required>
</label></p>

<p><label>Description
  <textarea name="description" maxlength="1000">{{ old('description', $ev->description ?? '') }}</textarea>
</label></p>

<p><label>Starts at
  <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($ev->starts_at ?? null)->format('Y-m-d\TH:i')) }}" required>
</label></p>

<p><label>Ends at
  <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($ev->ends_at ?? null)->format('Y-m-d\TH:i')) }}">
</label></p>

<p><label>Location
  <input name="location" value="{{ old('location', $ev->location ?? '') }}" maxlength="255" required>
</label></p>

<p><label>Online Venue
  <select name="is_online" required>
    @php($isOnline = (int) old('is_online', $ev->is_online ?? 0))
      <option value="0" @selected($isOnline === 0)>No</option>
      <option value="1" @selected($isOnline === 1)>Yes</option>
  </select>
</label>

<label>Online URL (if applicable)
  <input name="online_url" value="{{ old('online_url', $ev->online_url ?? '') }}" maxlength="255">
</label></p>

<p><label>Capacity
  <input type="number" name="capacity" min="1" max="1000" value="{{ old('capacity', $ev->capacity ?? '') }}" required>
</label></p>

<p><label>Price (cents)
  <input type="number" name="price_cents" min="0" step="1" value="{{ old('price_cents', $ev->price_cents ?? 0) }}" required>
</label></p>

<p><label>Event Image (if applicable)
  <input name="image_path" value="{{ old('image_path', $ev->image_path ?? '') }}" maxlength="255">
</label></p>