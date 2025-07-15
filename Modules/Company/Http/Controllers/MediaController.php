<?php

namespace Modules\Company\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Media;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = 'desc';

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $mediasQuery = Media::query()
            ->with(['document_media']);

        $mediasQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%')
                    ->orWhere('type_media', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('link', 'like', '%' . $request->search . '%');
            });
        });

        // Status filter
        $mediasQuery->when($request->status, function ($query, $status) {
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

        // Filter by type
        if ($request->filled('type')) {
            $mediasQuery->where('type_media', $request->type);
        }

        // Date filters
        if ($request->filled('from_date')) {
            $mediasQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $mediasQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        // Order
        $mediasQuery->orderBy($orderByColumn, $orderByDirection);

        // Paginate
        $medias = $mediasQuery->paginate($perPage, ['*'], 'page', $page);

        // Status counts (for totals)
        $statusCounts = DB::table('medias')
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

        // Response totals
        $responseTotals = [
            'all' => (int) $statusCounts->total,
            'active' => (int) $statusCounts->active,
            'in_active' => (int) $statusCounts->in_active,
        ];

        // Final JSON Response with pagination structure
        return response()->json([
            'data' => $medias->items(),
            'current_page' => $medias->currentPage(),
            'last_page' => $medias->lastPage(),
            'per_page' => $medias->perPage(),
            'total' => $medias->total(),
            'totals' => $responseTotals,
        ]);
    }


    public function nearestMedia(Request $request)
    {
        $now = now();
        $tenDaysAgo = $now->copy()->subDays(10);
        $orderByColumn = 'created_at';
        $orderByDirection = 'desc';

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        // Query untuk documentation media
        $documentationQuery = Media::query()
            ->with(['document_media'])
            ->where('type_media', 'documentation')
            ->where('created_at', '>=', $tenDaysAgo)
            ->where('created_at', '<=', $now)
            ->whereNull('deleted_at');

        $documentationQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%')
                    ->orWhere('type_media', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('link', 'like', '%' . $request->search . '%');
            });
        });

        $documentationMedia = $documentationQuery->orderBy($orderByColumn, $orderByDirection)->limit(3)->get();

        // Query untuk performance media
        $performanceQuery = Media::query()
            ->with(['document_media'])
            ->where('type_media', 'performance')
            ->where('created_at', '>=', $tenDaysAgo)
            ->where('created_at', '<=', $now)
            ->whereNull('deleted_at');

        $performanceQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%')
                    ->orWhere('type_media', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('link', 'like', '%' . $request->search . '%');
            });
        });

        $performanceMedia = $performanceQuery->orderBy($orderByColumn, $orderByDirection)->limit(3)->get();

        // Hitung totals
        $statusCounts = DB::table('medias')
            ->selectRaw('
                SUM(CASE WHEN type_media = "documentation" AND deleted_at IS NULL THEN 1 ELSE 0 END) as documentation,
                SUM(CASE WHEN type_media = "performance" AND deleted_at IS NULL THEN 1 ELSE 0 END) as performance
            ')
            ->where('created_at', '>=', $tenDaysAgo)
            ->where('created_at', '<=', $now)
            ->when(!empty($request->search), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('created_at', 'like', '%' . $request->search . '%')
                        ->orWhere('type_media', 'like', '%' . $request->search . '%')
                        ->orWhere('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%')
                        ->orWhere('link', 'like', '%' . $request->search . '%');
                });
            })
            ->first();

        $responseTotals = [
            'documentation' => (int) $statusCounts->documentation,
            'performance' => (int) $statusCounts->performance,
        ];

        return response()->json([
            'data' => [
                'documentation' => $documentationMedia,
                'performance' => $performanceMedia,
            ],
            'totals' => $responseTotals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('company::create');
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
        $media = Media::with(['document_media'])->findOrFail($id);

        return [
            'data' => $media,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $media = Media::with(['document_media'])->findOrFail($id);

        return [
            'data' => $media,
        ];
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
        //
    }
}
