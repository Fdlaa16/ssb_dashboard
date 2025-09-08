<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\StandingResource;
use App\Models\Club;
use App\Models\Standing;
use App\Models\Structure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StandingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $orderByColumn = $request->input('sort_by', 'points');
        $orderByDirection = $request->input('sort', 'desc');

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        $standingQuery = Standing::query()
            ->with([
                'club',
            ])
            ->withTrashed();

        $standingQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('club', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                });
        });

        $standingQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_active':
                    $query->onlyTrashed();
                    break;
                case 'active':
                    $query->whereNull('deleted_at');
                    break;
                case 'all':
                default:
                    break;
            }
        });

        $standingQuery->when($request->club_id, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('club', function ($q1) use ($request) {
                    $q1->where('id', $request->club_id);
                });
            });
        });

        $standingQuery->when($request->stadium_id, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('club', function ($q1) use ($request) {
                    $q1->where('id', $request->club_id);
                });
            });
        });

        $standingQuery->orderBy($orderByColumn, $orderByDirection);

        $scheduleMatch = $standingQuery->get();

        $statusCounts = DB::table('standings')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as in_active
            ')
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
            })
            ->first();

        $responseTotals = [
            'all' => (int) $statusCounts->total,
            'active' => (int) $statusCounts->active,
            'in_active' => (int) $statusCounts->in_active,
        ];

        return response()->json([
            'data' => $scheduleMatch,
            'totals' => $responseTotals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('dashboard::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $standing = Standing::withTrashed()->find($id);

        if (!$standing) {
            return response()->json(['message' => 'Klasemen tidak ditemukan'], 404);
        }

        $standing->delete();

        return response()->json([
            'message' => 'Klasemen berhasil dihapus.',
            'data' => new StandingResource($standing),
        ]);
    }

    public function active(Request $request, $id)
    {
        $standing = standing::withTrashed()->find($id);

        if (!$standing) {
            return response()->json(['message' => 'Klasemen tidak ditemukan'], 404);
        }

        $standing->restore();

        return response()->json([
            'message' => 'Klasemen berhasil diaktifkan.',
            'data' => new StandingResource($standing),
        ]);
    }

    public function export(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $standingQuery = Standing::query()
            ->with(['club:id,name'])
            ->withTrashed();

        $standingQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('club', function ($club) use ($request) {
                    $club->where('name', 'like', '%' . $request->search . '%');
                });
        });

        $standingQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_active':
                    $query->onlyTrashed();
                    break;
                case 'active':
                    $query->whereNull('deleted_at');
                    break;
            }
        });

        $standingQuery->when($request->club_id, function ($query) use ($request) {
            $query->where('club_id', $request->club_id);
        });

        if ($request->filled('from_date')) {
            $standingQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }
        if ($request->filled('to_date')) {
            $standingQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $standingQuery->orderBy($orderByColumn, $orderByDirection);

        // --- Excel Export ---
        $exportDir = 'StandingExport';
        if (!is_dir(Storage::disk('public')->path($exportDir))) {
            mkdir(Storage::disk('public')->path($exportDir), 0775, true);
            chmod(Storage::disk('public')->path($exportDir), 0775);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'A1' => 'Club',
            'B1' => 'Total',
            'C1' => 'Menang',
            'D1' => 'Seri',
            'E1' => 'Kalah',
            'F1' => 'Gol Masuk',
            'G1' => 'Gol Kebobolan',
            'H1' => 'Selisih Gol',
            'I1' => 'Poin',
            'J1' => 'Status',
        ];
        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        // Column width
        $columnWidths = [
            'A' => 25,
            'B' => 10,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 12,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 15,
        ];
        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Isi data
        $row = 2;
        $standingQuery->chunk(1000, function ($standings) use ($sheet, &$row) {
            foreach ($standings as $standing) {
                $statusText = isset($standing->deleted_at) ? 'Tidak Aktif' : 'Aktif';

                $sheet->setCellValue("A{$row}", $standing->club->name ?? '-');
                $sheet->setCellValue("B{$row}", $standing->total ?? 0);
                $sheet->setCellValue("C{$row}", $standing->win ?? 0);
                $sheet->setCellValue("D{$row}", $standing->draw ?? 0);
                $sheet->setCellValue("E{$row}", $standing->lose ?? 0);
                $sheet->setCellValue("F{$row}", $standing->goal_in ?? 0);
                $sheet->setCellValue("G{$row}", $standing->goal_conceded ?? 0);
                $sheet->setCellValue("H{$row}", $standing->goal_difference ?? 0);
                $sheet->setCellValue("I{$row}", $standing->points ?? 0);
                $sheet->setCellValue("J{$row}", $statusText);

                $row++;
            }
        });

        // Save file
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "standings_Export_{$timestamp}.xlsx";
        $filePath = Storage::disk('public')->path($exportDir . '/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $fileHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return response()->download($filePath, $fileName, $fileHeaders)->deleteFileAfterSend();
    }
}
