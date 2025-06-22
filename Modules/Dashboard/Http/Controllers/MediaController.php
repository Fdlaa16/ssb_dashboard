<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\MediaResource;
use App\Models\File;
use App\Models\Media;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        $mediasQuery = Media::query()
            ->withTrashed();

        $mediasQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('created_at', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('hashtag', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('link', 'like', '%' . $request->search . '%');
            });
        });

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

        if ($request->filled('from_date')) {
            $mediasQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $mediasQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $mediasQuery->orderBy($orderByColumn, $orderByDirection);

        $medias = $mediasQuery->get();

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

        $responseTotals = [
            'all' => (int) $statusCounts->total,
            'active' => (int) $statusCounts->active,
            'in_active' => (int) $statusCounts->in_active,
        ];

        return response()->json([
            'data' => $medias,
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
                'start_date' => 'required',
                'end_date' => 'required',
                'name'     => 'required',
                'title' => 'required',
                'hashtag' => 'required',
                'description' => 'required',
                'link' => 'required',
            ];

            $messages = [
                'start_date.required' => 'Tanggal mulai harus diisi',
                'end_date.required' => 'Tanggal akhir harus diisi',
                'name.required'     => 'Nama harus diisi',
                'title.required'    => 'Judul harus diisi',
                'hashtag.required'  => 'Hashtag harus diisi',
                'description.required' => 'Deskripsi harus diisi',
                'link.required'     => 'Link harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $media = Media::create([
                    'name' => $request->name,
                    'title' => $request->title,
                    'hashtag' => $request->hashtag,
                    'description' => $request->description,
                    'link' => $request->link,
                    'start_date' => Helper::formatDate($request->start_date, 'Y-m-d'),
                    'end_date' => Helper::formatDate($request->end_date, 'Y-m-d'),
                ]);

                $types = ['document_media'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $media->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');

                        $media->files()->where('type', $type)->delete();

                        $media->files()->create([
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
                    'message' => 'Media created successfully.',
                    'data' => $media
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
        $medias = Media::query()
            ->with([
                'document_media',
            ])
            ->find($id);

        $data = [
            'data' => $medias,
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

        $media = Media::find($id);

        if (!$media) {
            return response()->json(['message' => 'Media tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'start_date' => 'required',
                'end_date' => 'required',
                'name'     => 'required',
                'title' => 'required',
                'hashtag' => 'required',
                'description' => 'required',
                'link' => 'required',
            ];

            $messages = [
                'start_date.required' => 'Tanggal mulai harus diisi',
                'end_date.required' => 'Tanggal akhir harus diisi',
                'name.required'     => 'Nama harus diisi',
                'title.required'    => 'Judul harus diisi',
                'hashtag.required'  => 'Hashtag harus diisi',
                'description.required' => 'Deskripsi harus diisi',
                'link.required'     => 'Link harus diisi',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $media->update([
                    'name'   => $request->name,
                    'title' => $request->title,
                    'hashtag' => $request->hashtag,
                    'description' => $request->description,
                    'link' => $request->link,
                    'start_date' => Helper::formatDate($request->start_date, 'Y-m-d'),
                    'end_date' => Helper::formatDate($request->end_date, 'Y-m-d'),
                ]);

                $types = ['document_media'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $media->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $media->files()->where('type', $type)->delete();

                        $media->files()->create([
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
                    'message' => 'Media updated successfully.',
                    'data' => $media,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui media.',
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
        $media = Media::withTrashed()->find($id);

        if (!$media) {
            return response()->json(['message' => 'media tidak ditemukan'], 404);
        }

        $media->delete();

        return response()->json([
            'message' => 'media berhasil dihapus.',
            'data' => new MediaResource($media),
        ]);
    }

    public function active(Request $request, $id)
    {
        $media = Media::withTrashed()->find($id);

        if (!$media) {
            return response()->json(['message' => 'media tidak ditemukan'], 404);
        }

        $media->restore();

        return response()->json([
            'message' => 'media berhasil diaktifkan.',
            'data' => new MediaResource($media),
        ]);
    }
}
