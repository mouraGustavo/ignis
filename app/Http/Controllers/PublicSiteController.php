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
    /**
     * URLs padrao (marca / edicoes anteriores) mantidas exatamente como no
     * front original (ignis-front/src/pages/Home.jsx), hospedadas no CDN do
     * Base44 — nao ha necessidade de baixar/self-host esses assets.
     */
    private const LOGO_URL = 'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/7597405dd_image.png';

    private const BRASAO_URL = 'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/c6172d8de_brasoSP.png';

    private const CHURCH_URL = 'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/0dcfb3ec1_image.png';

    private const DEFAULT_GALLERY = [
        'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/41d0d569b_image.png',
        'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/c40046b33_image.png',
        'https://media.base44.com/images/public/6a58e2062aa1365f75b0f66c/7227d859f_image.png',
    ];

    public function home(): View
    {
        $settings = Setting::allAsArray();

        $mediaUrls = MediaItem::query()
            ->where('active', true)
            ->where('type', 'photo')
            ->orderBy('order')
            ->pluck('url')
            ->all();

        $galleryPhotos = array_values(array_unique([...self::DEFAULT_GALLERY, ...$mediaUrls]));

        $confirmados = EventoInscrito::query()
            ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
            ->count();

        $vagasEsgotadas = $confirmados >= (int) $settings['max_spots'];

        return view('home', [
            'settings' => $settings,
            'galleryPhotos' => $galleryPhotos,
            'logoUrl' => self::LOGO_URL,
            'brasaoUrl' => self::BRASAO_URL,
            'churchUrl' => self::CHURCH_URL,
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
