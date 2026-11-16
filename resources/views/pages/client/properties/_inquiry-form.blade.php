{{--
    The inquiry box on a property page: send an inquiry, or ask to view the
    property at a preferred date and time. Works for guests and signed-in buyers.
--}}
@php
    $user = auth()->user();
    $field = 'w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
    $invalid = fn (string $name) => $errors->has($name) ? 'ring-2 ring-red-400' : '';
    $messages = ['inquiry' => $property->inquiryMessage(), 'viewing' => $property->viewingMessage()];
    $mode = old('type') === 'viewing' ? 'viewing' : 'inquiry';
@endphp
<form action="{{ $user ? route('client.account.inquiries.store', $property->id) : route('client.property.inquire', $property) }}" method="POST" class="space-y-3"
      x-data="{
          mode: @js($mode),
          messages: @js($messages),
          message: @js(old('message', $messages[$mode])),
          edited: @js(old('message') !== null),
          switchTo(next) {
              if (! this.edited) this.message = this.messages[next];
              this.mode = next;
          },
      }">
    @csrf
    <input type="hidden" name="type" :value="mode" value="{{ $mode }}">

    <div class="grid grid-cols-2 gap-1 rounded-xl bg-stone-100 p-1" role="tablist" aria-label="What would you like to do?">
        @foreach(['inquiry' => 'Send Inquiry', 'viewing' => 'Schedule Viewing'] as $value => $text)
        <button type="button" role="tab" @click="switchTo('{{ $value }}')" :aria-selected="mode === '{{ $value }}'"
                :class="mode === '{{ $value }}' ? 'bg-white text-teal-800 shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-xs font-semibold transition">
            @if($value === 'inquiry')
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            @else
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            @endif
            {{ $text }}
        </button>
        @endforeach
    </div>

    <div>
        <label for="inquiry-name" class="text-xs text-stone-500 mb-1 block">Full Name{{ $user ? '' : ' *' }}</label>
        @if($user)
        <input id="inquiry-name" type="text" value="{{ $user->name }}" disabled class="{{ $field }} bg-stone-50">
        @else
        <input id="inquiry-name" type="text" name="name" placeholder="Jane Doe" required value="{{ old('name') }}" class="{{ $field }} {{ $invalid('name') }}">
        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        @endif
    </div>
    <div>
        <label for="inquiry-email" class="text-xs text-stone-500 mb-1 block">Email *</label>
        <input id="inquiry-email" type="email" name="email" placeholder="juan@email.com" required value="{{ old('email', $user?->email) }}" class="{{ $field }} {{ $invalid('email') }}">
        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="inquiry-phone" class="text-xs text-stone-500 mb-1 block">Phone *</label>
        <input id="inquiry-phone" type="tel" name="phone" placeholder="+63 912 345 6789" required value="{{ old('phone', $user?->phone) }}" class="{{ $field }} {{ $invalid('phone') }}">
        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="inquiry-facebook" class="text-xs text-stone-500 mb-1 block">Facebook <span class="text-stone-400">(optional)</span></label>
        <input id="inquiry-facebook" type="text" name="facebook" maxlength="255" placeholder="Name or profile link" value="{{ old('facebook') }}" class="{{ $field }}">
        @error('facebook') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="inquiry-message" class="text-xs text-stone-500 mb-1 block">Message *</label>
        <textarea id="inquiry-message" name="message" rows="4" required x-model="message" @input="edited = true" class="{{ $field }} resize-none {{ $invalid('message') }}">{{ old('message', $messages[$mode]) }}</textarea>
        @error('message') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div x-show="mode === 'viewing'" @if($mode !== 'viewing') x-cloak @endif class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
        <div>
            <label for="preferred-date" class="text-xs text-stone-500 mb-1 block">Preferred Date *</label>
            <input id="preferred-date" type="date" name="preferred_date" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}"
                   :required="mode === 'viewing'" :disabled="mode !== 'viewing'" @disabled($mode !== 'viewing') class="{{ $field }} {{ $invalid('preferred_date') }}">
            @error('preferred_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="preferred-time" class="text-xs text-stone-500 mb-1 block">Preferred Time *</label>
            <input id="preferred-time" type="time" name="preferred_time" value="{{ old('preferred_time') }}"
                   :required="mode === 'viewing'" :disabled="mode !== 'viewing'" @disabled($mode !== 'viewing') class="{{ $field }} {{ $invalid('preferred_time') }}">
            @error('preferred_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <p class="text-xs text-stone-500 sm:col-span-2 lg:col-span-1 xl:col-span-2">The agent will confirm the schedule with you.</p>
    </div>

    <button type="submit" class="flex w-full items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-xl text-sm font-semibold transition">
        <span x-text="mode === 'viewing' ? 'Schedule Viewing' : 'Send Inquiry'">{{ $mode === 'viewing' ? 'Schedule Viewing' : 'Send Inquiry' }}</span>
    </button>
</form>
