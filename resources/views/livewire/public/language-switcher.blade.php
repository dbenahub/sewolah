<div class="flex items-center gap-1 text-xs font-bold">
  <button wire:click="switchTo('ms')" class="px-2 py-1 rounded {{ app()->getLocale()==='ms' ? 'bg-brand-red text-white' : 'text-white/50' }}">BM</button>
  <span class="text-white/30">/</span>
  <button wire:click="switchTo('en')" class="px-2 py-1 rounded {{ app()->getLocale()==='en' ? 'bg-brand-red text-white' : 'text-white/50' }}">EN</button>
</div>
