<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\StadiumResource;
use App\Models\Stadium;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StadiumController extends Controller
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

        $stadiumQuery = Stadium::query()
            ->withTrashed();

        $stadiumQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%')
                ->orWhere('name', 'like', '%' . $request->search . '%')
                ->orWhere('area', 'like', '%' . $request->search . '%');
        });

        $stadiumQuery->when($request->status, function ($query, $status) {
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

        if ($request->filled('from_date')) {
            $stadiumQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $stadiumQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $stadiumQuery->orderBy($orderByColumn, $orderByDirection);

        $clubs = $stadiumQuery->get();

        $statusCounts = DB::table('stadiums')
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
            'data' => $clubs,
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
        DB::beginTransaction();

        try {
            $postData = $request->all();
            $rules = [
                'name'     => 'required',
                'area'     => 'required',
            ];

            $messages = [
                'name.required'     => 'Nama harus diisi',
                'area.required'     => 'Area harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $stadium = Stadium::create([
                    'name'      => $request->name,
                    'area'      => $request->area,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Stadium created successfully.',
                    'data' => $stadium
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
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
        $stadiums = Stadium::query()
            ->find($id);

        $data = [
            'data' => $stadiums,
        ];

        return $data;
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $stadium = Stadium::find($id);

        if (!$stadium) {
            return response()->json(['message' => 'Stadium tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'name'   => 'required',
                'area'   => 'required',
            ];

            $messages = [
                'name.required'     => 'Nama harus diisi',
                'area.required'     => 'Area harus diisi',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $stadium->update([
                    'name'       => $request->name,
                    'area'       => $request->area,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Stadium updated successfully.',
                    'data' => $stadium,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui stadium.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $stadium = Stadium::withTrashed()->find($id);

        if (!$stadium) {
            return response()->json(['message' => 'Stadium tidak ditemukan'], 404);
        }

        $stadium->delete();

        return response()->json([
            'message' => 'Stadium berhasil dihapus.',
            'data' => new StadiumResource($stadium),
        ]);
    }

    public function active(Request $request, $id)
    {
        $stadium = Stadium::withTrashed()->find($id);

        if (!$stadium) {
            return response()->json(['message' => 'Stadium tidak ditemukan'], 404);
        }

        $stadium->restore();

        return response()->json([
            'message' => 'Stadium berhasil diaktifkan.',
            'data' => new StadiumResource($stadium),
        ]);
    }
}
