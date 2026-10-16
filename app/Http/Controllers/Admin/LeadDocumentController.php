<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadDocument;
use Illuminate\Support\Facades\Storage;

/** Streams private customer documents to logged-in admins only. */
class LeadDocumentController extends Controller
{
    public function show(Lead $lead, LeadDocument $document)
    {
        abort_unless($document->lead_id === $lead->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        $name = $lead->referenceNumber().'-'.$document->type.'.'.pathinfo($document->path, PATHINFO_EXTENSION);

        return Storage::disk('local')->response($document->path, $name, [
            'Content-Type' => $document->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ], 'inline');
    }
}
