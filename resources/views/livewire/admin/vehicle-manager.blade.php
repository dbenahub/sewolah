<div class="grid gap-6">
  <h1 class="m-0 text-2xl font-extrabold">Pengurusan Kenderaan</h1>

  <form wire:submit="save" class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4 max-w-xl">
    <p class="m-0 text-[15px] font-bold">{{ $editingId ? 'Edit Kenderaan' : 'Tambah Kenderaan Baharu' }}</p>
    <div class="grid sm:grid-cols-2 gap-4">
      <label class="grid gap-1.5 text-[13px] font-semibold">Nama Kenderaan
        <input type="text" wire:model="name" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      </label>
      <label class="grid gap-1.5 text-[13px] font-semibold">Kategori
        <input type="text" wire:model="category" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      </label>
    </div>
    <label class="grid gap-1.5 text-[13px] font-semibold">Tags (BM) — pisah dengan koma
      <input type="text" wire:model="tagsMs" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">Tags (EN) — comma separated
      <input type="text" wire:model="tagsEn" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">CTA Label
      <input type="text" wire:model="ctaLabel" class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
    </label>
    <label class="grid gap-1.5 text-[13px] font-semibold">Gambar Kenderaan
      <input type="file" wire:model="newImage" class="text-sm">
    </label>
    <div class="flex gap-5 text-[13px]">
      <label class="flex gap-2 items-center"><input type="checkbox" wire:model="isFeatured"> Featured</label>
      <label class="flex gap-2 items-center"><input type="checkbox" wire:model="isActive"> Active</label>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="bg-brand-red text-white font-bold text-[13.5px] px-5.5 py-3 rounded-lg">SIMPAN</button>
      @if($editingId)
        <button type="button" wire:click="resetForm" class="bg-transparent border border-white/15 text-white text-[13.5px] font-bold px-5.5 py-3 rounded-lg">Batal</button>
      @endif
    </div>
  </form>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($vehicles as $v)
      <div class="bg-[#141414] border border-white/8 rounded-2xl p-5 grid gap-2.5">
        <p class="m-0 font-bold">{{ $v->name }}</p>
        <p class="m-0 text-xs text-white/50">{{ $v->category }}</p>
        <div class="flex gap-2 mt-1.5">
          <button wire:click="edit({{ $v->id }})" class="text-xs bg-white/10 px-3 py-1.5 rounded-lg">Edit</button>
          <button wire:click="delete({{ $v->id }})" wire:confirm="Padam kenderaan ini?" class="text-xs bg-red-600/20 text-red-300 px-3 py-1.5 rounded-lg">Padam</button>
        </div>
      </div>
    @endforeach
  </div>
</div>
