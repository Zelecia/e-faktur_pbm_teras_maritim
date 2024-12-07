<?php

namespace App\Http\Controllers;

use App\Models\Faktur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function pdf($id)
    {
        // Ambil data faktur dengan relasinya
        $faktur = Faktur::with(['tipeFaktur', 'pelanggan', 'penandatangan', 'referensi', 'referensi.uraianBarang'])
            ->findOrFail($id);

        // Hitung subtotal dan total PPN
        $subtotal = $faktur->dpp;
        $ppn = $faktur->ppn;

        $logoPath = public_path('images/logo.png');
        $qrCodePath = public_path('images/qr-code.png');

        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($qrCodePath));

        // Render view ke PDF
        $pdf = Pdf::loadView('pdf.faktur', [
            'faktur' => $faktur,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'total' => $subtotal + $ppn,
            'logoBase64' => $logoBase64,
            'qrCodeBase64' => $qrCodeBase64,
        ]);

        // Unduh atau tampilkan PDF
        return $pdf->stream("Faktur-{$faktur->nomor}.pdf");
    }
}
