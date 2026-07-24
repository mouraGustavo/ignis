<?php

namespace App\Http\Controllers;

use App\Models\EventoInscrito;
use App\Models\MediaItem;
use App\Models\Setting;
use App\Models\StatusInscricao;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        $settings = Setting::allAsArray();

        $galleryPhotos = MediaItem::query()
            ->where('active', true)
            ->where('type', 'photo')
            ->orderBy('order')
            ->pluck('url')
            ->all();

        $confirmados = EventoInscrito::query()
            ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
            ->count();

        $vagasEsgotadas = $confirmados >= (int) $settings['max_spots'];

        return view('home', [
            'settings' => $settings,
            'galleryPhotos' => $galleryPhotos,
            'logoUrl' => $settings['logo_url'],
            'brasaoUrl' => $settings['brasao_url'],
            'churchUrl' => $settings['church_url'],
            'vagasEsgotadas' => $vagasEsgotadas,
        ]);
    }

    public function equipe(): View
    {
        return view('equipe', [
            'members' => TeamMember::query()->orderBy('order')->get(),
        ]);
    }

    public function adminDashboard(): View
    {
        $counts = EventoInscrito::query()
            ->join('status_inscricao', 'status_inscricao.id', '=', 'evento_inscritos.status_id')
            ->selectRaw('status_inscricao.nome as status, count(*) as total')
            ->groupBy('status_inscricao.nome')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => [
                'total' => (int) $counts->sum(),
                'confirmed' => (int) ($counts[StatusInscricao::CONFIRMADA] ?? 0),
                'pending' => (int) ($counts[StatusInscricao::PENDENTE] ?? 0),
                'cancelled' => (int) ($counts[StatusInscricao::CANCELADA] ?? 0),
            ],
        ]);
    }
}
