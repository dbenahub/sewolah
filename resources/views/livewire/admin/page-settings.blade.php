<div class="grid gap-6 max-w-xl">
  <h1 class="m-0 text-2xl font-extrabold">Edit Landing Page</h1>
  <p class="m-0 text-[13px] text-white/50">Kemaskini maklumat hubungan yang digunakan pada Landing Page (nombor WhatsApp &amp; email notifikasi).</p>
  <form wire:submit="save" class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4">
    <label class="grid gap-1.5 text-[13px] font-semibold">Nombor WhatsApp Admin
      <input type="text" wire:model="whatsappNumber" placeholder="60123456789" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      @error('whatsappNumber') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">Email Admin (untuk notifikasi)
      <input type="email" wire:model="adminEmail" placeholder="sewolah@gmail.com" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      @error('adminEmail') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
    </label>
    <button type="submit" class="justify-self-start bg-brand-red text-white font-bold text-[13.5px] px-5.5 py-3 rounded-lg">SIMPAN</button>
    @if($saved)<p class="m-0 text-[12.5px] text-green-400">Disimpan &amp; digunakan pada Landing Page.</p>@endif
    <a href="{{ route('landing') }}" target="_blank" class="justify-self-start text-[13px] text-brand-red font-bold">Buka Landing Page →</a>
  </form>
</div>
