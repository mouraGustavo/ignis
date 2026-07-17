<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminRegistrationsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return Excel::download(new AdminRegistrationsExport(), 'inscricoes.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
