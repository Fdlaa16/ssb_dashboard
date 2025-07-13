<?php

namespace Modules\Company\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\SlideHomeResource;
use App\Models\File;
use App\Models\SlideHome;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SlideHomeController extends Controller
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

        $slideHomesQuery = SlideHome::query()
            ->with([
                'slide_home'
            ]);

        $slideHomesQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%');
            });
        });

        if ($request->filled('from_date')) {
            $slideHomesQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $slideHomesQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $slideHomesQuery->orderBy($orderByColumn, $orderByDirection);

        $slideHomes = $slideHomesQuery->get();

        $statusCounts = DB::table('slide_homes')
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
            'data' => $slideHomes,
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
                'slide_home' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'slide_home.image' => 'Slide Home harus berupa gambar',
                'slide_home.mimes' => 'Format Slide Home harus jpeg, png, jpg, atau bmp',
                'slide_home.max' => 'Ukuran Slide Home maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()->toArray()], 422);
            } else {
                $slideHome = SlideHome::create([]);

                $types = ['slide_home'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $slideHome->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $slideHome->files()->where('type', $type)->delete();

                        $slideHome->files()->create([
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
                    'message' => 'Slide Home created successfully.',
                    'data' => $slideHome->load('files') // Load relasi files
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
        $slideHome = SlideHome::query()
            ->with([
                'files' => function ($query) {
                    $query->where('type', 'slide_home')
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->find($id);

        $data = [
            'data' => $slideHome,
        ];

        return response()->json($data);
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

        $slideHome = SlideHome::find($id);

        if (!$slideHome) {
            return response()->json(['message' => 'Slide Home tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'slide_home.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'slide_home.*.image' => 'Slide Home harus berupa gambar',
                'slide_home.*.mimes' => 'Format Slide Home harus jpeg, png, jpg, atau bmp',
                'slide_home.*.max' => 'Ukuran Slide Home maksimal 2MB',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $types = ['slide_home'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $slideHome->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $slideHome->files()->where('type', $type)->delete();

                        $slideHome->files()->create([
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
                    'message' => 'Slide Home updated successfully.',
                    'data' => $slideHome,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui slide$slideHome.',
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
        $slideHome = SlideHome::withTrashed()->find($id);

        if (!$slideHome) {
            return response()->json(['message' => 'Slide Home tidak ditemukan'], 404);
        }

        $slideHome->delete();

        return response()->json([
            'message' => 'Slide Home berhasil dihapus.',
            'data' => new SlideHomeResource($slideHome),
        ]);
    }

    public function active(Request $request, $id)
    {
        $slideHome = SlideHome::withTrashed()->find($id);

        if (!$slideHome) {
            return response()->json(['message' => 'Slide Home tidak ditemukan'], 404);
        }

        $slideHome->restore();

        return response()->json([
            'message' => 'Slide Home berhasil diaktifkan.',
            'data' => new SlideHomeResource($slideHome),
        ]);
    }
}
