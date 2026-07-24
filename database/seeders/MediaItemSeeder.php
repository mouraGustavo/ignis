<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use Illuminate\Database\Seeder;

class MediaItemSeeder extends Seeder
{
    /**
     * Fotos padrao da galeria (antes hardcoded/hospedadas no CDN do Base44 em
     * PublicSiteController). Seedadas como MediaItem normais para que fiquem
     * gerenciaveis (trocar/remover) em /admin/midias, como qualquer outra midia.
     */
    public function run(): void
    {
        $defaults = [
            ['title' => 'Galeria - foto padrão 1', 'url' => '/storage/site/galeria-1.png', 'order' => 0],
            ['title' => 'Galeria - foto padrão 2', 'url' => '/storage/site/galeria-2.png', 'order' => 1],
            ['title' => 'Galeria - foto padrão 3', 'url' => '/storage/site/galeria-3.png', 'order' => 2],
        ];

        foreach ($defaults as $default) {
            MediaItem::query()->firstOrCreate(
                ['url' => $default['url']],
                ['title' => $default['title'], 'type' => 'photo', 'order' => $default['order'], 'active' => true]
            );
        }
    }
}
