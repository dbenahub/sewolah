<div class="grid gap-6 max-w-xl">
  <h1 class="m-0 text-2xl font-extrabold">{{ __('admin.change_password.title') }}</h1>
  <p class="m-0 text-[13px] text-white/50">{{ __('admin.change_password.subtitle') }}</p>
  <form wire:submit="save" class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4">
    <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('admin.change_password.current_password') }}
      <input type="password" wire:model="current_password" autocomplete="current-password"
             class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      @error('current_password')<span class="text-[12.5px] text-red-400">{{ $message }}</span>@enderror
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('admin.change_password.new_password') }}
      <input type="password" wire:model="password" autocomplete="new-password"
             class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      @error('password')<span class="text-[12.5px] text-red-400">{{ $message }}</span>@enderror
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('admin.change_password.confirm_password') }}
      <input type="password" wire:model="password_confirmation" autocomplete="new-password"
             class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <button type="submit" class="justify-self-start bg-brand-red text-white font-bold text-[13.5px] px-5.5 py-3 rounded-lg">
      {{ __('admin.change_password.submit') }}
    </button>
    @if($saved)<p class="m-0 text-[12.5px] text-green-400">{{ __('admin.change_password.success') }}</p>@endif
  </form>
</div>
