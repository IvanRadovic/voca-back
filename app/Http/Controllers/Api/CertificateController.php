<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Certificates owned by the authenticated user.
     */
    public function mine(Request $request)
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with(['call.nvo.nvo'])
            ->latest('issued_at')
            ->get();

        return CertificateResource::collection($certificates);
    }

    /**
     * Public verification by code.
     */
    public function show(string $code)
    {
        $certificate = Certificate::where('code', $code)
            ->with(['user', 'call.nvo.nvo'])
            ->firstOrFail();

        return new CertificateResource($certificate);
    }

    /**
     * Downloadable PDF certificate.
     */
    public function download(string $code)
    {
        $certificate = Certificate::where('code', $code)
            ->with(['user', 'call.nvo.nvo'])
            ->firstOrFail();

        $data = [
            'recipient' => $certificate->user->name,
            'title' => $certificate->call->title,
            'organization' => optional($certificate->call->nvo->nvo)->organization_name
                ?? optional($certificate->call->nvo)->name,
            'date' => $certificate->issued_at->format('d F Y'),
            'code' => $certificate->code,
            'verifyUrl' => rtrim(config('app.frontend_url'), '/').'/sertifikat/'.$certificate->code,
        ];

        $pdf = Pdf::loadView('certificate', $data)->setPaper('a4', 'landscape');

        return $pdf->download('voca-certificate-'.$certificate->code.'.pdf');
    }
}
