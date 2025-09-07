@php($ev = $event ?? null)

<div class="mb-3">
  <label for="title" class="form-label">Title</label>
  <input type="text" name="title" class="form-control" maxlength="100" value="{{ old('title', $ev->title ?? '') }}" required>
</div>

<div class="mb-3">
  <label for="description" class="form-label">Description</label>
  <textarea name="description" class="form-control" maxlength="1000" rows="3">{{ old('description', $ev->description ?? '') }}</textarea>
</div>

<div class="row mb-3">
  <div class="col">
    <label for="starts_at" class="form-label">Starts At</label>
    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($ev->starts_at)->format('Y-m-d\TH:i')) }}" required>
  </div>

  <div class="col">
    <label for="ends_at" class="form-label">Ends At</label>
    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($ev->ends_at)->format('Y-m-d\TH:i')) }}">
  </div>
</div>

<div class="mb-3">
  <label for="location" class="form-label">Location</label>
  <input type="text" name="location" class="form-control" maxlength="255" value="{{ old('location', $ev->location ?? '') }}">
</div>

<div class="row mb-3">
  <div class="col">
    <label for="is_online" class="form-label">Online Venue</label>
    @php($isOnline = (int) old('is_online', $ev->is_online ?? 0))
    <select name="is_online" class="form-select">
      <option value="0" @selected($isOnline === 0)>No</option>
      <option value="1" @selected($isOnline === 1)>Yes</option>
    </select>
  </div>
  <div class="col">
    <label for="online_url" class="form-label">Online URL (if applicable)</label>
    <input type="text" name="online_url" class="form-control" maxlength="255" value="{{ old('online_url', $ev->online_url ?? '') }}">
  </div>
</div>

<div class="row mb-3">
  <div class="col">
    <label for="capacity" class="form-label">Capacity</label>
    <input type="number" name="capacity" class="form-control" min="1" max="1000" value="{{ old('capacity', $ev->capacity ?? '') }}">
  </div>
  <div class="col">
    <label for="price_cents" class="form-label">Price (cents)</label>
    <input type="number" name="price_cents" class="form-control" min="0" value="{{ old('price_cents', $ev->price_cents ?? 0) }}">
  </div>
</div>

<div class="mb-3">
  <label for="image_path" class="form-label">Event Image (if applicable)</label>
  <input type="text" name="image_path" class="form-control" maxlength="255" value="{{ old('image_path', $ev->image_path ?? '') }}">
</div>
