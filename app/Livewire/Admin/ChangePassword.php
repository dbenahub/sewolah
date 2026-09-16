<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $saved = false;

    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => __('admin.change_password.errors.current_required'),
            'current_password.current_password' => __('admin.change_password.errors.current_invalid'),
            'password.required' => __('admin.change_password.errors.password_required'),
            'password.min' => __('admin.change_password.errors.password_min'),
            'password.confirmed' => __('admin.change_password.errors.password_confirmed'),
        ];
    }

    public function updated($property)
    {
        $this->saved = false;
    }

    public function save()
    {
        $this->saved = false;

        $this->validate();

        $user = Auth::user();
        $user->forceFill([
            'password' => $this->password,
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.change-password')->layout('layouts.admin');
    }
}
