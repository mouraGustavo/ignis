<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Services\FileUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Configuracoes extends Component
{
    use WithFileUploads;

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

    public string $logo_url = '';

    public string $brasao_url = '';

    public string $church_url = '';

    public $logo_file = null;

    public $brasao_file = null;

    public $church_file = null;

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
        $this->logo_url = $settings['logo_url'];
        $this->brasao_url = $settings['brasao_url'];
        $this->church_url = $settings['church_url'];
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
            'logo_file' => ['nullable', 'image', 'max:5120'],
            'brasao_file' => ['nullable', 'image', 'max:5120'],
            'church_file' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function save(FileUploadService $fileUploadService): void
    {
        $this->saved = false;
        $data = $this->validate();
        $data['registration_open'] = $this->registration_open ? 'true' : 'false';

        if ($this->logo_file) {
            $data['logo_url'] = $fileUploadService->storePublic($this->logo_file, 'site');
        }

        if ($this->brasao_file) {
            $data['brasao_url'] = $fileUploadService->storePublic($this->brasao_file, 'site');
        }

        if ($this->church_file) {
            $data['church_url'] = $fileUploadService->storePublic($this->church_file, 'site');
        }

        unset($data['logo_file'], $data['brasao_file'], $data['church_file']);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        $this->logo_url = $data['logo_url'] ?? $this->logo_url;
        $this->brasao_url = $data['brasao_url'] ?? $this->brasao_url;
        $this->church_url = $data['church_url'] ?? $this->church_url;
        $this->reset(['logo_file', 'brasao_file', 'church_file']);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.configuracoes');
    }
}
