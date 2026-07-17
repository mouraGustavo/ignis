<?php

namespace App\Livewire\Admin;

use App\Models\TeamMember;
use App\Services\FileUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Equipe extends Component
{
    use WithFileUploads;

    public bool $adding = false;

    public string $name = '';

    public string $role = '';

    public string $bio = '';

    public $photo = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    public function add(FileUploadService $fileUploadService): void
    {
        $this->validate();

        $photoUrl = $this->photo ? $fileUploadService->storePublic($this->photo, 'team') : null;

        TeamMember::create([
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio ?: null,
            'photo_url' => $photoUrl,
            'order' => TeamMember::query()->count(),
        ]);

        $this->reset(['name', 'role', 'bio', 'photo']);
        $this->adding = false;
    }

    public function delete(int $id): void
    {
        TeamMember::query()->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.equipe', [
            'members' => TeamMember::query()->orderBy('order')->get(),
        ]);
    }
}
