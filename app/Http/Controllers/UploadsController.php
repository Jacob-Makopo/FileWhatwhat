<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Uploads; // Changed from Upload to Uploads
use App\Models\Company;
use App\Models\Municipality;
use App\Models\MunicipalityDeadline;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UploadsExport;

class UploadsController extends Controller
{
    /**
     * Display the uploads page
     */
    public function index(Request $request)
    {
        $this->authorize('view uploads');

        $query = Uploads::query() // Changed from Upload to Uploads
            ->with(['company:id,name', 'municipality:id,name'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$s%"))
                  ->orWhereHas('municipality', fn($m) => $m->where('name', 'like', "%$s%"));
            })
            ->latest('submitted_at');

        // Get pending deadlines information (only if user can view deadlines)
        $pendingDeadlines = [];
        if ($request->user()->hasPermissionTo('view deadlines')) {
            $municipalities = Municipality::with(['companies', 'deadlines'])->get();
            
            foreach ($municipalities as $municipality) {
                $upcomingDeadline = $municipality->deadlines()
                    ->where('deadline_date', '>=', now())
                    ->orderBy('deadline_date')
                    ->first();
                    
                if ($upcomingDeadline) {
                    $submittedCompanyIds = Uploads::where('municipality_id', $municipality->id) // Changed from Upload to Uploads
                        ->where('submitted_at', '>=', now()->subDays(30))
                        ->pluck('company_id')
                        ->unique()
                        ->toArray();
                        
                    $pendingCompanies = $municipality->companies()
                        ->whereNotIn('id', $submittedCompanyIds)
                        ->get();
                        
                    if ($pendingCompanies->count() > 0) {
                        $pendingDeadlines[] = [
                            'municipality' => $municipality->name,
                            'deadline_date' => $upcomingDeadline->deadline_date->format('Y-m-d'),
                            'pending_companies' => $pendingCompanies->pluck('name'),
                            'pending_count' => $pendingCompanies->count(),
                            'total_companies' => $municipality->companies->count(),
                        ];
                    }
                }
            }
        }

        return Inertia::render('Uploads/Index', [
            'filters' => $request->only(['status', 'search']),
            'submissions' => $query->paginate(12)->withQueryString(),
            'companies' => Company::all(),
            'municipalities' => Municipality::all(),
            'pendingDeadlines' => $pendingDeadlines,
        ]);
    }

    /**
     * Store multiple uploads
     */
    public function store(Request $request)
    {
        $this->authorize('create upload');

        $request->validate([
            'company_ids' => 'required|array',
            'company_ids.*' => 'exists:companies,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'original_files' => 'required|array',
            'original_files.*' => 'file|max:10240',
            'workings_file' => 'nullable|file|max:10240',
            'systems_import_file' => 'nullable|file|max:10240',
        ]);

        $uploadedSubmissions = [];

        // Store single files
        $workingsFilePath = $request->hasFile('workings_file') 
            ? $request->file('workings_file')->store('uploads', 'public') 
            : null;

        $systemsImportFilePath = null;
        $systemImportDate = null;
        if ($request->hasFile('systems_import_file')) {
            $systemsImportFile = $request->file('systems_import_file');
            $systemsImportFilePath = $systemsImportFile->store('uploads', 'public');
            $systemImportDate = now();
        }

        $originalFileData = [];
        $originalFileNames = [];
        $extractedDates = [];

        foreach ($request->original_files as $index => $originalFile) {
            $originalFilePath = $originalFile->store('uploads', 'public');
            $extension = strtolower($originalFile->getClientOriginalExtension());
            $fileName = $originalFile->getClientOriginalName();
            
            $extractedDate = $this->extractDateFromFile($originalFilePath, $extension);
            
            $originalFileData[] = $originalFilePath;
            $originalFileNames[] = $fileName;
            $extractedDates[] = $extractedDate ? $extractedDate->toDateTimeString() : null;
            
            Log::info("File {$index}: {$fileName}, Extracted date: " . ($extractedDate ? $extractedDate->toDateTimeString() : 'null'));
        }

        $workingsFileName = $request->hasFile('workings_file') 
            ? $request->file('workings_file')->getClientOriginalName()
            : null;

        $systemsImportFileName = $request->hasFile('systems_import_file')
            ? $request->file('systems_import_file')->getClientOriginalName()
            : null;

        foreach ($request->company_ids as $companyId) {
            $upload = Uploads::create([ // Changed from Upload to Uploads
                'reference' => strtoupper(Str::random(10)),
                'company_id' => $companyId,
                'municipality_id' => $request->municipality_id,
                'status' => 'Pending',
                'original_file_path' => $originalFileData,
                'original_file_names' => $originalFileNames,
                'workings_file_path' => $workingsFilePath,
                'workings_file_name' => $workingsFileName,
                'systems_import_file_path' => $systemsImportFilePath,
                'systems_import_file_name' => $systemsImportFileName,
                'submitted_at' => now(),
                'extracted_dates' => $extractedDates,
                'system_import_date' => $systemImportDate,
            ]);

            $uploadedSubmissions[] = $upload;
        }

        return redirect()->route('uploads.index')
            ->with('success', count($uploadedSubmissions) . ' upload(s) submitted successfully.');
    }

    private function extractDateFromFile($filePath, $extension)
    {
        $fileContent = Storage::disk('public')->get($filePath);

        if ($extension === 'eml') {
            return $this->extractDateFromEml($fileContent);
        } elseif ($extension === 'msg') {
            return $this->extractDateFromMsg($fileContent);
        }

        return null;
    }

    private function extractDateFromEml($content)
    {
        $headers = $this->parseEmailHeaders($content);
        $dateString = $headers['date'] ?? null;
        
        if ($dateString) {
            try {
                return Carbon::parse($dateString);
            } catch (\Exception $e) {
                return $this->parseAlternativeDateFormats($dateString);
            }
        }
        
        return null;
    }

    private function parseEmailHeaders($content)
    {
        $headers = [];
        $lines = preg_split('/\r\n|\r|\n/', $content);
        
        foreach ($lines as $line) {
            if (trim($line) === '') break;
            
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $headerName = strtolower(trim($matches[1]));
                $headerValue = trim($matches[2]);
                $headers[$headerName] = $headerValue;
            }
        }
        
        return $headers;
    }

    private function extractDateFromMsg($content)
    {
        $headerPatterns = [
            '/\bDate:\s*([^\r\n]+)/i',
            '/\bSent:\s*([^\r\n]+)/i',
            '/\bCreation-Date:\s*([^\r\n]+)/i',
            '/\bDelivery-Date:\s*([^\r\n]+)/i',
            '/\bReceived:\s*([^\r\n]+)/i',
        ];
        
        foreach ($headerPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $dateString = trim($matches[1]);
                if (!empty($dateString)) {
                    try {
                        return Carbon::parse($dateString);
                    } catch (\Exception $e) {
                        Log::info("Failed to parse header date '{$dateString}', trying alternative formats");
                        $parsed = $this->parseAlternativeDateFormats($dateString);
                        if ($parsed) return $parsed;
                    }
                }
            }
        }
        
        $datePatterns = [
            '/\b\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\b/',
            '/\b\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}\b/',
            '/\b\d{1,2}\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{2,4}\b/i',
            '/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{1,2},?\s+\d{2,4}\b/i',
        ];
        
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $dateString = trim($matches[0]);
                try {
                    return Carbon::parse($dateString);
                } catch (\Exception $e) {
                    Log::info("Failed to parse content date '{$dateString}', trying alternative formats");
                    $parsed = $this->parseAlternativeDateFormats($dateString);
                    if ($parsed) return $parsed;
                }
            }
        }
        
        Log::warning("No date could be extracted from MSG file content");
        return null;
    }

    private function parseAlternativeDateFormats($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        $cleaned = preg_replace([
            '/\([^)]+\)/',
            '/[A-Z]{3,5}\s*-\s*\d{4}/',
            '/\b(Mon|Tue|Wed|Thu|Fri|Sat|Sun)[a-z]*,\s*/i',
            '/\s+/',
        ], ' ', $dateString);
        
        $cleaned = trim($cleaned);
        
        try {
            return Carbon::parse($cleaned);
        } catch (\Exception $e) {
            $formats = [
                'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
                'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
                'd.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y',
                'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
                'M j, Y H:i:s', 'M j, Y', 'j M Y H:i:s', 'j M Y',
            ];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $cleaned);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }

    public function destroy(Uploads $upload) // Changed from Upload to Uploads
    {
        $this->authorize('delete upload');

        if ($upload->original_file_path) {
            $originalFiles = is_array($upload->original_file_path) 
                ? $upload->original_file_path 
                : json_decode($upload->original_file_path, true);
                
            if (is_array($originalFiles)) {
                foreach ($originalFiles as $filePath) {
                    if ($filePath) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }
        }
        
        if ($upload->workings_file_path) {
            Storage::disk('public')->delete($upload->workings_file_path);
        }
        
        if ($upload->systems_import_file_path) {
            Storage::disk('public')->delete($upload->systems_import_file_path);
        }

        $upload->delete();

        return redirect()->route('uploads.index')->with('success', 'Upload deleted successfully.');
    }

    /**
     * Display uploads history
     */
    public function history(Request $request)
    {
        $this->authorize('view uploads');

        $query = Uploads::query() // Changed from Upload to Uploads
            ->with(['company:id,name', 'municipality:id,name'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$s%"))
                  ->orWhereHas('municipality', fn($m) => $m->where('name', 'like', "%$s%"));
            })
            ->latest('submitted_at');

        return Inertia::render('Uploads/History', [
            'filters' => $request->only(['status', 'search']),
            'uploads' => $query->paginate(20)->withQueryString(),
            'statusOptions' => ['Pending', 'Processing', 'Completed', 'Rejected'],
        ]);
    }

    /**
     * Export uploads to Excel
     */
    public function export(Request $request)
    {
        $this->authorize('export uploads');

        $uploads = Uploads::with(['company', 'municipality']) // Changed from Upload to Uploads
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$s%"))
                  ->orWhereHas('municipality', fn($m) => $m->where('name', 'like', "%$s%"));
            })
            ->latest('submitted_at')
            ->get();

        return Excel::download(new UploadsExport($uploads), 'uploads-history-' . now()->format('Y-m-d') . '.xlsx');
    }
}
