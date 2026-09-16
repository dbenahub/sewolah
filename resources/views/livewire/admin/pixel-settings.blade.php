<div class="grid gap-6 max-w-xl">
  <h1 class="m-0 text-2xl font-extrabold">Tetapan Pixel &amp; Tracking</h1>
  <p class="m-0 text-[13px] text-white/50">Masukkan Pixel ID untuk setiap platform. ID ini akan digunakan pada Landing Page.</p>
  <form wire:submit="save" class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4">
    <label class="grid gap-1.5 text-[13px] font-semibold">Facebook / Meta Pixel ID
      <input type="text" wire:model="meta" placeholder="Contoh: 1234567890123456" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">Google Ads Conversion ID
      <input type="text" wire:model="google" placeholder="Contoh: AW-123456789" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">TikTok Pixel ID
      <input type="text" wire:model="tiktok" placeholder="Contoh: CXXXXXXXXXXXXXX" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <button type="submit" class="justify-self-start bg-brand-red text-white font-bold text-[13.5px] px-5.5 py-3 rounded-lg">SIMPAN</button>
    @if($saved)<p class="m-0 text-[12.5px] text-green-400">Disimpan.</p>@endif
  </form>
</div>
