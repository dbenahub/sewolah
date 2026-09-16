<div>
@if($submitted)
  <div class="bg-white rounded-[20px] p-10 text-center grid gap-3.5 border border-black/8">
    <p class="m-0 text-4xl">✓</p>
    <p class="m-0 text-2xl font-extrabold">{{ __('landing.booking.thanks_title') }}</p>
    <p class="m-0 text-sm leading-relaxed text-black/60">{{ __('landing.booking.thanks_copy') }}</p>
  </div>
@else
  <div class="text-center mb-8">
    <h2 class="m-0 mb-2.5 text-3xl md:text-5xl font-extrabold">{{ __('landing.booking.title') }}</h2>
    <p class="m-0 mb-2 text-base font-semibold">{{ __('landing.booking.subtitle') }}</p>
    <p class="m-0 text-sm text-black/55 max-w-lg mx-auto">{{ __('landing.booking.copy') }}</p>
  </div>

  <form wire:submit="submit" class="bg-white rounded-[20px] p-6 md:p-10 border border-black/8 grid gap-5.5">
    {{-- honeypot --}}
    <input type="text" wire:model="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

    <div class="flex justify-between items-center">
      <p class="m-0 text-[13px] font-bold tracking-wide text-brand-red">{{ __('landing.booking.step_label', ['step' => $step]) }}</p>
      <div class="flex gap-1.5">
        @for($i=1;$i<=3;$i++)
          <span class="w-7 h-1 rounded {{ $i <= $step ? 'bg-brand-red' : 'bg-black/10' }}"></span>
        @endfor
      </div>
    </div>

    @if($step === 1)
      <div class="grid gap-4">
        <p class="m-0 text-base font-extrabold">{{ __('landing.booking.step1_title') }}</p>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.origin') }}
          <input type="text" wire:model="origin" placeholder="{{ __('landing.booking.fields.origin_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          @error('origin') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </label>
        <div class="grid sm:grid-cols-2 gap-4">
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.airport') }}
            <select wire:model="airport" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
              <option value="">--</option>
              @foreach(__('landing.booking.airport_options') as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
            </select>
            @error('airport') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
          </label>
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.purpose') }}
            <select wire:model="purpose" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
              <option value="">--</option>
              @foreach(__('landing.booking.purpose_options') as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
            </select>
            @error('purpose') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
          </label>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.arrival_date') }}
            <input type="date" wire:model="arrivalDate" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          </label>
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.arrival_time') }}
            <input type="time" wire:model="arrivalTime" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          </label>
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.end_date') }}
            <input type="date" wire:model="endDate" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
            @error('endDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
          </label>
        </div>
        <button type="button" wire:click="goNext" class="bg-black text-white font-bold text-[13.5px] px-5 py-3.5 rounded-lg">{{ __('landing.booking.next') }}</button>
      </div>
    @endif

    @if($step === 2)
      <div class="grid gap-4">
        <p class="m-0 text-base font-extrabold">{{ __('landing.booking.step2_title') }}</p>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.vehicle') }}
          <select wire:model="vehicleId" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
            <option value="">--</option>
            @foreach($this->vehicleOptions as $opt)<option value="{{ $opt->id }}">{{ $opt->name }}</option>@endforeach
          </select>
          @error('vehicleId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.other_vehicle') }}
          <input type="text" wire:model="otherVehicleModel" placeholder="{{ __('landing.booking.fields.other_vehicle_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
        </label>
        <div class="grid sm:grid-cols-2 gap-4">
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.passengers') }}
            <input type="number" min="1" wire:model="passengers" placeholder="{{ __('landing.booking.fields.passengers_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
            @error('passengers') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
          </label>
          <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.luggage') }}
            <select wire:model="luggage" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
              <option value="">--</option>
              @foreach(__('landing.booking.luggage_options') as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
            </select>
          </label>
        </div>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.destination') }}
          <input type="text" wire:model="destination" placeholder="{{ __('landing.booking.fields.destination_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          @error('destination') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </label>
        <div class="flex gap-3">
          <button type="button" wire:click="goBack" class="bg-transparent text-black font-bold text-[13.5px] px-5 py-3.5 rounded-lg border border-black/15">{{ __('landing.booking.back') }}</button>
          <button type="button" wire:click="goNext" class="flex-1 bg-black text-white font-bold text-[13.5px] px-5 py-3.5 rounded-lg">{{ __('landing.booking.next') }}</button>
        </div>
      </div>
    @endif

    @if($step === 3)
      <div class="grid gap-4">
        <p class="m-0 text-base font-extrabold">{{ __('landing.booking.step3_title') }}</p>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.full_name') }}
          <input type="text" wire:model="fullName" placeholder="{{ __('landing.booking.fields.full_name_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          @error('fullName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.phone') }}
          <input type="tel" wire:model="phone" placeholder="{{ __('landing.booking.fields.phone_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm">
          @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('landing.booking.fields.notes') }}
          <textarea wire:model="notes" rows="3" placeholder="{{ __('landing.booking.fields.notes_ph') }}" class="w-full box-border px-3.5 py-3 rounded-lg border border-black/15 text-sm resize-y"></textarea>
        </label>
        <label class="flex gap-2.5 items-start text-[13px] leading-relaxed text-black/70">
          <input type="checkbox" wire:model="consent" class="mt-0.5 w-4 h-4">
          {{ __('landing.booking.fields.consent') }}
        </label>
        @error('consent') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        <div class="flex gap-3">
          <button type="button" wire:click="goBack" class="bg-transparent text-black font-bold text-[13.5px] px-5 py-3.5 rounded-lg border border-black/15">{{ __('landing.booking.back') }}</button>
          <button type="submit" class="flex-1 bg-brand-red text-white font-bold text-[13.5px] px-5 py-3.5 rounded-lg">{{ __('landing.booking.submit') }}</button>
        </div>
      </div>
    @endif

    <p class="m-0 text-[11.5px] leading-relaxed text-black/45">{{ __('landing.booking.disclaimer') }}</p>
  </form>
@endif
</div>
