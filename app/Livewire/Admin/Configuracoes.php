<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Configuracoes extends Component
{
    public string $site_title = '';

    public string $site_subtitle = '';

    public string $event_date = '';

    public string $event_location = '';

    public string $hero_image_url = '';

    public string $max_spots = '150';

    public string $registration_value = '150';

    public bool $registration_open = true;

    public string $pix_key = '';

    public string $pix_name = '';

    public string $coordinator_email = '';

    public string $instagram_url = '';

    public bool $saved = false;

    public function mount(): void
    {
        $settings = Setting::allAsArray();

        $this->site_title = $settings['site_title'];
        $this->site_subtitle = $settings['site_subtitle'];
        $this->event_date = $settings['event_date'];
        $this->event_location = $settings['event_location'];
        $this->hero_image_url = $settings['hero_image_url'];
        $this->max_spots = $settings['max_spots'];
        $this->registration_value = $settings['registration_value'];
        $this->registration_open = $settings['registration_open'] === 'true';
        $this->pix_key = $settings['pix_key'];
        $this->pix_name = $settings['pix_name'];
        $this->coordinator_email = $settings['coordinator_email'];
        $this->instagram_url = $settings['instagram_url'];
    }

    protected function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:255'],
            'site_subtitle' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'hero_image_url' => ['nullable', 'string', 'max:500'],
            'max_spots' => ['required', 'integer', 'min:1'],
            'registration_value' => ['required', 'numeric', 'min:0'],
            'pix_key' => ['nullable', 'string', 'max:255'],
            'pix_name' => ['nullable', 'string', 'max:255'],
            'coordinator_email' => ['nullable', 'email', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->saved = false;
        $data = $this->validate();
        $data['registration_open'] = $this->registration_open ? 'true' : 'false';

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.configuracoes');
    }
}
