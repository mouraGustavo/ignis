<?php

namespace App\Livewire\Admin;

use App\Models\MediaItem;
use App\Services\FileUploadService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Midias extends Component
{
    use WithFileUploads;

    public bool $adding = false;

    public string $type = 'photo';

    public string $title = '';

    public string $url = '';

    public $file = null;

    public function updatedFile(): void
    {
        $this->url = '';
    }

    protected function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['photo', 'video'])],
            'file' => ['required_without:url', 'nullable', 'file', 'mimes:jpg,jpeg,png,mp4', 'max:20480'],
            'url' => ['required_without:file', 'nullable', 'url'],
        ];
    }

    public function add(FileUploadService $fileUploadService): void
    {
        $this->validate();

        $url = $this->url;

        if ($this->file) {
            $url = $fileUploadService->storePublic($this->file, 'media');
        }

        MediaItem::create([
            'title' => $this->title ?: null,
            'type' => $this->type,
            'url' => $url,
            'order' => MediaItem::query()->count(),
            'active' => true,
        ]);

        $this->reset(['title', 'url', 'file']);
        $this->type = 'photo';
        $this->adding = false;
    }

    public function toggleActive(int $id): void
    {
        $media = MediaItem::query()->findOrFail($id);
        $media->update(['active' => ! $media->active]);
    }

    public function delete(int $id): void
    {
        MediaItem::query()->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.midias', [
            'medias' => MediaItem::query()->orderBy('order')->get(),
        ]);
    }
}
