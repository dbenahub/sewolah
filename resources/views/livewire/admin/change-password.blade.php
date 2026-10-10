<div class="adm-grid" style="gap:20px;max-width:620px">
  <div class="adm-head">
    <div>
      <h1>{{ __('admin.change_password.title') }}</h1>
      <p>{{ __('admin.change_password.subtitle') }}</p>
    </div>
  </div>
  <form wire:submit="save" class="adm-card adm-form">
    <label class="adm-field"><span class="adm-label">{{ __('admin.change_password.current_password') }}</span>
      <input type="password" wire:model="current_password" autocomplete="current-password" class="adm-input">
      @error('current_password')<span class="adm-error">{{ $message }}</span>@enderror
    </label>
    <label class="adm-field"><span class="adm-label">{{ __('admin.change_password.new_password') }}</span>
      <input type="password" wire:model="password" autocomplete="new-password" class="adm-input">
      @error('password')<span class="adm-error">{{ $message }}</span>@enderror
    </label>
    <label class="adm-field"><span class="adm-label">{{ __('admin.change_password.confirm_password') }}</span>
      <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="adm-input">
    </label>
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <button type="submit" class="adm-btn adm-btn--primary">{{ __('admin.change_password.submit') }}</button>
      @if($saved)<span class="adm-alert adm-alert--success" style="padding:8px 12px">{{ __('admin.change_password.success') }}</span>@endif
    </div>
  </form>
</div>
