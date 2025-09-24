@php($ev = $event ?? null)

<!-- Title and Description -->
<div class="mb-3">
  <label for="title" class="form-label">Title</label>
  <input type="text" name="title" class="form-control" maxlength="100" value="{{ old('title', $ev->title ?? '') }}" required>
</div>

<div class="mb-3">
  <label for="description" class="form-label">Description</label>
  <textarea name="description" class="form-control" maxlength="1000" rows="3">{{ old('description', $ev->description ?? '') }}</textarea>
</div>

<!-- Time (Starts at & Ends at) -->
<div class="row mb-3">
  <div class="col">
    <label for="starts_at" class="form-label">Starts At</label>
    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $event->starts_at ? $event->starts_at->format('Y-m-d\TH:i') : '') }}" required>
  </div>

  <div class="col">
    <label for="ends_at" class="form-label">Ends At</label>
    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $event->ends_at ? $event->ends_at->format('Y-m-d\TH:i') : '') }}">
  </div>
</div>

<!-- Venue (Location and Online URL) -->
<div class="mb-3">
  <label>Online Venue</label>
  <input type="hidden" name="is_online" value="0">
  <input type="checkbox" name="is_online" value="1" @checked(old('is_online', $ev->is_online ?? 0))> {{-- Default 0 if no existing data --}}
</div>

<div class="row mb-3">
  <div class="col">
    <label for="location" class="form-label">Location</label>
    <input type="text" name="location" class="form-control" maxlength="255" value="{{ old('location', $ev->location ?? '') }}"> {{-- Validation enforces required when is_online=0 --}}
  </div>
  <div class="col">
    <label for="online_url" class="form-label">Online URL (if applicable)</label>
    <input type="text" name="online_url" class="form-control" maxlength="255" value="{{ old('online_url', $ev->online_url ?? '') }}">
  </div>
</div>

<!-- Capacity & Price(in cents) -->
<div class="row mb-3">
  <div class="col">
    <label for="capacity" class="form-label">Capacity (1~1000)</label>
    <input type="number" name="capacity" class="form-control" min="1" max="1000" value="{{ old('capacity', $ev->capacity ?? '') }}" required>
  </div>
  <div class="col">
    <label for="price_cents" class="form-label">Price (cents)</label>
    <input type="number" name="price_cents" class="form-control" min="0" value="{{ old('price_cents', $ev->price_cents ?? 0) }}"> {{-- Default 0(Free) if no existing data --}}
  </div>
</div>

<!-- Images -->
<div class="mb-3">
  <label for="image_path" class="form-label">Event Image (if applicable)</label>
  <input type="text" name="image_path" class="form-control" maxlength="255" value="{{ old('image_path', $ev->image_path ?? '') }}">
</div>
