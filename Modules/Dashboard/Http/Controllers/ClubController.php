<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use App\Models\File;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClubController extends Controller
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

        $clubsQuery = Club::query()
            ->withTrashed();

        $clubsQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        });

        if ($request->filled('from_date')) {
            $clubsQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $clubsQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $clubsQuery->orderBy($orderByColumn, $orderByDirection);

        $clubs = $clubsQuery->get();

        $statusCounts = DB::table('clubs')
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
            ];

            $messages = [
                'name.required'     => 'Nama harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $club = Club::create([
                    'name' => $request->name,
                ]);

                $types = ['profile_club'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $club->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');

                        $club->files()->where('type', $type)->delete();

                        $club->files()->create([
                            'type' => $type,
                            'name' => $fileName,
                            'original_name' => $file->getClientOriginalName(),
                            'extension' => $file->getClientOriginalExtension(),
                            'path' => "$fileDir$fileName",
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Club created successfully.',
                    'data' => $club
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

        $clubs = Club::query()
            ->with([
                'profile_club',
            ])
            ->find($id);

        $data = [
            'data' => $clubs,
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

        $club = Club::find($id);

        if (!$club) {
            return response()->json(['message' => 'Club tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'name'   => 'required',
            ];

            $messages = [
                'name.required'     => 'Nama harus diisi',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $club->update([
                    'name'   => $request->name,
                ]);

                $types = ['profile_club'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $club->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $club->files()->where('type', $type)->delete();

                        $club->files()->create([
                            'type'           => $type,
                            'name'           => $fileName,
                            'original_name'  => $file->getClientOriginalName(),
                            'extension'      => $file->getClientOriginalExtension(),
                            'path'           => $fileDir . $fileName,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Club updated successfully.',
                    'data' => $club,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui club.',
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
        $club = Club::withTrashed()->find($id);

        if (!$club) {
            return response()->json(['message' => 'Club tidak ditemukan'], 404);
        }

        $club->delete();

        return response()->json([
            'message' => 'Club berhasil dihapus.',
            'data' => new ClubResource($club),
        ]);
    }

    public function active(Request $request, $id)
    {
        $club = Club::withTrashed()->find($id);

        if (!$club) {
            return response()->json(['message' => 'Club tidak ditemukan'], 404);
        }

        $club->restore();

        return response()->json([
            'message' => 'Club berhasil diaktifkan.',
            'data' => new clubResource($club),
        ]);
    }
}
