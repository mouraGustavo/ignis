<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * GET /admin/files?path=responsaveis/documentos/uuid.pdf
     * Serve arquivos do disco privado ('local') apenas para admins autenticados
     * (protegido pelos middlewares 'auth' + 'admin' no grupo de rotas).
     */
    public function show(Request $request): StreamedResponse
    {
        $path = $request->validate(['path' => ['required', 'string']])['path'];

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
