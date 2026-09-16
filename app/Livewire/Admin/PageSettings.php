<?php

namespace App\Livewire\Admin;

use App\Models\PageSetting;
use Livewire\Component;

class PageSettings extends Component
{
    public string $whatsappNumber = '';
    public string $adminEmail = '';
    public bool $saved = false;

    public function mount()
    {
        $settings = PageSetting::current();
        $this->whatsappNumber = $settings->whatsapp_number;
        $this->adminEmail = $settings->admin_notification_email;
    }

    public function save()
    {
        $this->validate([
            'whatsappNumber' => ['required', 'string', 'max:20'],
            'adminEmail' => ['required', 'email'],
        ]);

        PageSetting::current()->update([
            'whatsapp_number' => $this->whatsappNumber,
            'admin_notification_email' => $this->adminEmail,
        ]);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.page-settings')->layout('layouts.admin');
    }
}
