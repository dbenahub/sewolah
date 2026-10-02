@php
    $d = fn ($text) => str_replace(':days', (string) $minDays, $text);
    $categoryIcons = [
        'individual' => '<path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM2.5 19c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5M14 14.3c.6-.2 1.3-.3 2-.3 2.4 0 4.3 1.6 4.8 4.4"/>',
        'corporate' => '<path d="M3.5 20.5h17M5.5 20.5V5.5l7-2v17M12.5 8.5h6v12M8.5 8h1M8.5 11.5h1M8.5 15h1M15 12h1M15 15.5h1"/>',
        'outstation' => '<path d="M2.5 16.5h19M6 13.5l-2.2-4.6 1.9-.6 3.4 3 4.3-1.4L9.9 4l2.2-.7 6.4 6.3 2.4-.8c.9-.3 1.8.2 2 1 .3.8-.2 1.6-1 1.9L7.4 15.4"/>',
        'event' => '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3Z"/>',
        'long_term' => '<path d="M4 6.5h16v13H4zM4 10.5h16M8.5 3.5v5M15.5 3.5v5M8 14h2M14 14h2M8 17h2"/>',
    ];
    $returnMin = $pickupDate && $pickupDate >= $earliestDate ? $pickupDate : $earliestDate;
@endphp
<div class="sw-formpage">
  <section class="sw-formhero">
    <div class="sw-container">
      <p class="sw-eyebrow sw-eyebrow--light">{{ __('form.eyebrow') }}</p>
      <h1 class="sw-formhero__title">{{ __('form.title') }}</h1>
      <p class="sw-formhero__intro">{{ __('form.intro') }}</p>
    </div>
  </section>

  <div class="sw-container sw-formwrap">
    {{-- ============ ASIDE ============ --}}
    <aside class="sw-formaside">
      <div class="sw-formaside__earliest">
        <span>{{ __('form.aside.earliest') }}</span>
        <strong>{{ $earliestLabel }}</strong>
      </div>

      <p class="sw-formaside__title">{{ __('form.aside.title') }}</p>
      <ul class="sw-formaside__points" role="list">
        @foreach(__('form.aside.points') as $point)
          <li>
            <strong>{{ $d($point['title']) }}</strong>
            <span>{{ $d($point['desc']) }}</span>
          </li>
        @endforeach
      </ul>

      <p class="sw-formaside__title">{{ __('form.aside.next_title') }}</p>
      <ol class="sw-formaside__next" role="list">
        @foreach(__('form.aside.next') as $line)
          <li>{{ $line }}</li>
        @endforeach
      </ol>
    </aside>

    {{-- ============ FORM CARD ============ --}}
    <div class="sw-formcard" id="borang">
      @if($submitted)
        <div class="sw-success"
             @if($whatsappUrl) x-data x-init="setTimeout(() => { window.location.href = @js($whatsappUrl) }, 3500)" @endif>
          <div class="sw-success__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
          </div>
          <p class="sw-eyebrow">{{ __('form.success.eyebrow') }}</p>
          <h2 class="sw-success__title">{{ __('form.success.title', ['name' => $fullName]) }}</h2>
          @if($reference)
            <p class="sw-success__ref">{{ __('form.success.ref') }}: <strong>{{ $reference }}</strong></p>
          @endif
          <p class="sw-success__copy">{{ __('form.success.copy', ['email' => $email]) }}</p>
          @if($whatsappUrl)
            <p class="sw-success__redirect">{{ __('form.success.redirect') }}</p>
            <a href="{{ $whatsappUrl }}" class="sw-btn sw-btn--whatsapp sw-btn--block" data-whatsapp-link>{{ __('form.success.cta') }}</a>
          @endif
        </div>
      @else
        <form wire:submit="submit" novalidate>
          {{-- honeypot --}}
          <div class="sw-hp" aria-hidden="true">
            <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
          </div>

          {{-- Stepper --}}
          <div class="sw-stepper">
            <p class="sw-stepper__label">{{ __('form.step_label', ['step' => $step]) }}</p>
            <ol class="sw-stepper__list" role="list">
              @foreach(__('form.steps') as $i => $label)
                <li @class(['is-active' => $step === $i + 1, 'is-done' => $step > $i + 1])>
                  <span class="sw-stepper__dot">{{ $i + 1 }}</span>
                  <span class="sw-stepper__text">{{ $label }}</span>
                </li>
              @endforeach
            </ol>
          </div>

          {{-- ============ STEP 1 ============ --}}
          @if($step === 1)
            <div class="sw-fstep" wire:key="step-1">
              <div class="sw-fstep__head">
                <h2>{{ __('form.step1.title') }}</h2>
                <p>{{ __('form.step1.subtitle') }}</p>
              </div>

              <div class="sw-cats" role="radiogroup" aria-label="{{ __('form.step1.title') }}">
                @foreach(\App\Models\Lead::CUSTOMER_CATEGORIES as $cat)
                  <button type="button"
                          wire:click="selectCategory('{{ $cat }}')"
                          role="radio"
                          aria-checked="{{ $customerCategory === $cat ? 'true' : 'false' }}"
                          data-category="{{ $cat }}"
                          @class(['sw-cat', 'is-selected' => $customerCategory === $cat])>
                    <span class="sw-cat__icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">{!! $categoryIcons[$cat] !!}</svg>
                    </span>
                    <span class="sw-cat__text">
                      <strong>{{ __('form.categories.'.$cat.'.title') }}</strong>
                      <span>{{ __('form.categories.'.$cat.'.desc') }}</span>
                    </span>
                    <span class="sw-cat__check" aria-hidden="true"></span>
                  </button>
                @endforeach
              </div>
              @error('customerCategory') <p class="sw-error">{{ $message }}</p> @enderror

              @if($customerCategory === 'corporate')
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.company_name') }} <em>*</em></span>
                  <input type="text" wire:model="companyName" placeholder="{{ __('form.fields.company_name_ph') }}" autocomplete="organization">
                  @error('companyName') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              @endif

              <div class="sw-factions">
                <button type="button" wire:click="goNext" class="sw-btn sw-btn--ink sw-btn--block" wire:loading.attr="disabled">{{ __('form.next') }} <span aria-hidden="true">→</span></button>
              </div>
            </div>
          @endif

          {{-- ============ STEP 2 ============ --}}
          @if($step === 2)
            <div class="sw-fstep" wire:key="step-2">
              <div class="sw-fstep__head">
                <h2>{{ __('form.step2.title') }}</h2>
                <p>{{ __('form.step2.subtitle') }}</p>
              </div>

              <div class="sw-grid sw-grid--2">
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.purpose') }} <em>*</em></span>
                  <select wire:model="purpose">
                    <option value="">{{ __('form.select') }}</option>
                    @foreach(__('form.purpose_options') as $opt)
                      <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                  </select>
                  @error('purpose') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.pickup_state') }} <em>*</em></span>
                  <select wire:model="pickupState">
                    <option value="">{{ __('form.select') }}</option>
                    @foreach(trans('form.states', [], 'ms') as $state)
                      <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                  </select>
                  @error('pickupState') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              </div>

              <div class="sw-grid sw-grid--2">
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.pickup_location') }} <em>*</em></span>
                  <input type="text" wire:model="pickupLocation" placeholder="{{ __('form.fields.pickup_location_ph') }}">
                  @error('pickupLocation') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.return_location') }}</span>
                  <input type="text" wire:model="returnLocation" placeholder="{{ __('form.fields.return_location_ph') }}">
                  @error('returnLocation') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              </div>

              <div class="sw-grid sw-grid--3">
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.pickup_date') }} <em>*</em></span>
                  <input type="date" wire:model.blur="pickupDate" min="{{ $earliestDate }}">
                  @error('pickupDate') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.pickup_time') }} <em>*</em></span>
                  <input type="time" wire:model="pickupTime">
                  @error('pickupTime') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.return_date') }} <em>*</em></span>
                  <input type="date" wire:model="returnDate" min="{{ $returnMin }}">
                  @error('returnDate') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              </div>
              <p class="sw-hint">{{ __('form.fields.earliest_hint', ['date' => $earliestLabel, 'days' => $minDays]) }}</p>

              <div class="sw-grid sw-grid--2">
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.vehicle') }} <em>*</em></span>
                  <select wire:model.live="vehicleId">
                    <option value="">{{ __('form.select') }}</option>
                    <option value="any">{{ __('form.fields.vehicle_any') }}</option>
                    @foreach($this->vehicleOptions as $v)
                      <option value="{{ $v->id }}">{{ $v->name }}{{ $v->category ? ' — '.$v->category : '' }}</option>
                    @endforeach
                    <option value="other">{{ __('form.fields.vehicle_other') }}</option>
                  </select>
                  @error('vehicleId') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.passengers') }} <em>*</em></span>
                  <input type="number" min="1" max="50" inputmode="numeric" wire:model="passengers" placeholder="{{ __('form.fields.passengers_ph') }}">
                  @error('passengers') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              </div>

              @if($vehicleId === 'other')
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.other_vehicle') }} <em>*</em></span>
                  <input type="text" wire:model="otherVehicleModel" placeholder="{{ __('form.fields.other_vehicle_ph') }}">
                  @error('otherVehicleModel') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              @endif

              <div class="sw-factions">
                <button type="button" wire:click="goBack" class="sw-btn sw-btn--ghost-dark">{{ __('form.back') }}</button>
                <button type="button" wire:click="goNext" class="sw-btn sw-btn--ink sw-btn--grow" wire:loading.attr="disabled">{{ __('form.next') }} <span aria-hidden="true">→</span></button>
              </div>
            </div>
          @endif

          {{-- ============ STEP 3 ============ --}}
          @if($step === 3)
            <div class="sw-fstep" wire:key="step-3">
              <div class="sw-fstep__head">
                <h2>{{ __('form.step3.title') }}</h2>
                <p>{{ __('form.step3.subtitle') }}</p>
              </div>

              <label class="sw-field">
                <span class="sw-field__label">{{ __('form.fields.full_name') }} <em>*</em></span>
                <input type="text" wire:model="fullName" placeholder="{{ __('form.fields.full_name_ph') }}" autocomplete="name">
                @error('fullName') <span class="sw-error">{{ $message }}</span> @enderror
              </label>

              <div class="sw-grid sw-grid--2">
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.phone') }} <em>*</em></span>
                  <input type="tel" wire:model="phone" placeholder="{{ __('form.fields.phone_ph') }}" autocomplete="tel" inputmode="tel">
                  @error('phone') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
                <label class="sw-field">
                  <span class="sw-field__label">{{ __('form.fields.email') }} <em>*</em></span>
                  <input type="email" wire:model="email" placeholder="{{ __('form.fields.email_ph') }}" autocomplete="email" inputmode="email">
                  @error('email') <span class="sw-error">{{ $message }}</span> @enderror
                </label>
              </div>

              <fieldset class="sw-field sw-radios">
                <legend class="sw-field__label">{{ __('form.fields.driver_license') }} <em>*</em></legend>
                @foreach(__('form.license_options') as $value => $label)
                  <label @class(['sw-radio', 'is-selected' => $driverLicense === $value])>
                    <input type="radio" name="driverLicense" value="{{ $value }}" wire:model.live="driverLicense">
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
                @error('driverLicense') <span class="sw-error">{{ $message }}</span> @enderror
              </fieldset>

              <label class="sw-field">
                <span class="sw-field__label">{{ __('form.fields.notes') }}</span>
                <textarea wire:model="notes" rows="3" placeholder="{{ __('form.fields.notes_ph') }}"></textarea>
                @error('notes') <span class="sw-error">{{ $message }}</span> @enderror
              </label>

              <label class="sw-consent-check">
                <input type="checkbox" wire:model="consent">
                <span>{{ __('form.fields.consent', ['days' => $minDays]) }}
                  <a href="{{ route('terms') }}" target="_blank" rel="noopener">{{ __('home.footer.terms') }}</a>
                  {{ __('form.fields.and') }}
                  <a href="{{ route('privacy-policy') }}" target="_blank" rel="noopener">{{ __('home.footer.privacy') }}</a>.
                </span>
              </label>
              @error('consent') <p class="sw-error">{{ $message }}</p> @enderror

              <div class="sw-factions">
                <button type="button" wire:click="goBack" class="sw-btn sw-btn--ghost-dark">{{ __('form.back') }}</button>
                <button type="submit" class="sw-btn sw-btn--red sw-btn--grow" wire:loading.attr="disabled" wire:target="submit">
                  <span wire:loading.remove wire:target="submit">{{ __('form.submit') }}</span>
                  <span wire:loading wire:target="submit">{{ __('form.submitting') }}</span>
                </button>
              </div>
            </div>
          @endif

          <p class="sw-disclaimer">{{ __('form.disclaimer') }}</p>
        </form>
      @endif
    </div>
  </div>
</div>
