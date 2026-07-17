<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Guarda um arquivo em disco publico (uso: foto do participante, midias do site).
     * Retorna a URL publica do arquivo.
     */
    public function storePublic(UploadedFile $file, string $folder): string
    {
        $path = $file->storeAs($folder, $this->generateFileName($file), 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Guarda um arquivo em disco PRIVADO (uso: documento do responsavel, comprovante de
     * pagamento). Nao fica acessivel diretamente via URL publica -- so via rota
     * autenticada (ver Admin\FileController), conforme recomendacao de seguranca do guia.
     * Retorna o path relativo (armazenado no banco), nao uma URL.
     */
    public function storePrivate(UploadedFile $file, string $folder): string
    {
        return $file->storeAs($folder, $this->generateFileName($file), 'local');
    }

    private function generateFileName(UploadedFile $file): string
    {
        return Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
    }
}
