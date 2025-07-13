<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\MediaResource;
use App\Models\File;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Contracts\Service\Attribute\Required;

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
                    ->orWhere('type_media', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('link', 'like', '%' . $request->search . '%');
            });
        });

        $mediasQuery->when($request->type_media, function ($query) use ($request) {
            $query->where('type_media', $request->type_media);
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
                'type_media' => 'required',
                'start_date' => 'required_if:type_media,documentation',
                'end_date' => 'required_if:type_media,documentation',
                // 'name' => 'required',
                'title' => 'required',
                // 'hashtag' => 'required',
                'description' => 'required',
                'link' => 'required',
                'document_media.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'type_media.required' => 'Tipe Media harus diisi',
                'start_date.required_if' => 'Tanggal mulai harus diisi',
                'end_date.required_if' => 'Tanggal akhir harus diisi',
                // 'name.required' => 'Nama harus diisi',
                'title.required' => 'Judul harus diisi',
                // 'hashtag.required' => 'Hashtag harus diisi',
                'description.required' => 'Deskripsi harus diisi',
                'link.required' => 'Link harus diisi',
                'document_media.*.image' => 'File harus berupa gambar',
                'document_media.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau bmp',
                'document_media.*.max' => 'Ukuran gambar maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($request->hasFile('document_media')) {
                $files = $request->file('document_media');
                if (is_array($files) && count($files) > 5) {
                    return response()->json([
                        'errors' => ['document_media' => ['Maksimal 5 gambar yang dapat diupload']]
                    ], 422);
                }
            }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()->toArray()], 422);
            } else {
                $media = Media::create([
                    'type_media' => $request->type_media,
                    'title' => $request->title,
                    // 'hashtag' => $request->hashtag,
                    'description' => $request->description,
                    'link' => $request->link,
                    'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
                ]);

                // Handle multiple file uploads
                if ($request->hasFile('document_media')) {
                    $files = $request->file('document_media');
                    $fileObj = new File();

                    // Jika hanya 1 file, konversi ke array
                    if (!is_array($files)) {
                        $files = [$files];
                    }

                    foreach ($files as $index => $file) {
                        if ($file && $file->isValid()) {
                            $fileDir = $fileObj->getDirectory('document_media');
                            $fileName = $fileObj->getFileName('document_media', $media->id, $file, $index);
                            $file->storeAs($fileDir, $fileName, 'public');

                            $media->files()->create([
                                'type' => 'document_media',
                                'name' => $fileName,
                                'original_name' => $file->getClientOriginalName(),
                                'extension' => $file->getClientOriginalExtension(),
                                'path' => "$fileDir$fileName",
                                'sort_order' => $index, // Untuk mengurutkan gambar
                            ]);
                        }
                    }
                }

                DB::commit();
                return response()->json([
                    'message' => 'Media created successfully.',
                    'data' => $media->load('files') // Load relasi files
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
        $media = Media::query()
            ->with([
                'files' => function ($query) {
                    $query->where('type', 'document_media')
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->find($id);

        $data = [
            'data' => $media,
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
        try {
            $media = Media::findOrFail($id);
            $postData = $request->all();

            $rules = [
                'type_media' => 'required',
                'start_date' => 'required_if:type_media,documentation',
                'end_date' => 'required_if:type_media,documentation',
                'title' => 'required',
                'description' => 'required',
                'link' => 'required',
                'document_media.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'removed_media_ids.*' => 'integer|exists:files,id',
            ];

            $messages = [
                'start_date.required_if' => 'Tanggal mulai harus diisi',
                'end_date.required_if' => 'Tanggal akhir harus diisi',
                'title.required' => 'Judul harus diisi',
                'description.required' => 'Deskripsi harus diisi',
                'link.required' => 'Link harus diisi',
                'document_media.*.image' => 'File harus berupa gambar',
                'document_media.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau bmp',
                'document_media.*.max' => 'Ukuran gambar maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            // Check total files after removal and addition
            $currentFiles = $media->files()->where('type', 'document_media')->count();
            $removedCount = $request->has('removed_media_ids') ? count($request->removed_media_ids) : 0;
            $newFilesCount = $request->hasFile('document_media') ? count($request->file('document_media')) : 0;
            $totalAfterUpdate = $currentFiles - $removedCount + $newFilesCount;

            if ($totalAfterUpdate > 5) {
                return response()->json([
                    'errors' => ['document_media' => ['Maksimal 5 gambar yang dapat diupload']]
                ], 422);
            }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()->toArray()], 422);
            }

            $media->update([
                'type_media' => $request->type_media,
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link,
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
            ]);

            // Handle removed files
            if ($request->has('removed_media_ids') && !empty($request->removed_media_ids)) {
                $filesToRemove = $media->files()
                    ->where('type', 'document_media')
                    ->whereIn('id', $request->removed_media_ids)
                    ->get();

                foreach ($filesToRemove as $file) {
                    Storage::disk('public')->delete($file->path);
                    $file->delete();
                }
            }

            // Handle new files
            if ($request->hasFile('document_media')) {
                $files = $request->file('document_media');
                $fileObj = new File();

                if (!is_array($files)) {
                    $files = [$files];
                }

                // Get current max sort_order if column exists, otherwise use count
                $maxSortOrder = 0;
                try {
                    // Check if sort_order column exists
                    $maxSortOrder = $media->files()
                        ->where('type', 'document_media')
                        ->max('sort_order') ?? 0;
                } catch (\Exception $e) {
                    // If sort_order column doesn't exist, use count as fallback
                    $maxSortOrder = $media->files()
                        ->where('type', 'document_media')
                        ->count();
                }

                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        $fileDir = $fileObj->getDirectory('document_media');
                        $fileName = $fileObj->getFileName('document_media', $media->id, $file, $maxSortOrder + $index + 1);
                        $file->storeAs($fileDir, $fileName, 'public');

                        $fileData = [
                            'type' => 'document_media',
                            'name' => $fileName,
                            'original_name' => $file->getClientOriginalName(),
                            'extension' => $file->getClientOriginalExtension(),
                            'path' => "$fileDir$fileName",
                        ];

                        // Only add sort_order if the column exists
                        if (Schema::hasColumn('files', 'sort_order')) {
                            $fileData['sort_order'] = $maxSortOrder + $index + 1;
                        }

                        $media->files()->create($fileData);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Media updated successfully.',
                'data' => $media->load('files')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
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
