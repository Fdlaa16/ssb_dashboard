<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\StructureResource;
use App\Models\File;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StructureController extends Controller
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

        $structuresQuery = Structure::query()
            ->with([
                'user',
            ])
            ->withTrashed();

        $structuresQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    });
            });
        });

        $structuresQuery->when($request->status, function ($query, $status) {
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
            $structuresQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $structuresQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $structuresQuery->orderBy($orderByColumn, $orderByDirection);

        $structures = $structuresQuery->get();

        $statusCounts = DB::table('structures')
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
            'data' => $structures,
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
                'email'             => 'required|email|unique:users,email',
                'name'              => 'required',
                'date_of_birth'     => 'required',
                'department'        => 'required',
                'avatar'            => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'            => 'Email harus diisi',
                'email.email'               => 'Format email tidak valid',
                'email.unique'              => 'Email sudah digunakan',
                'name.required'             => 'Nama harus diisi',
                'date_of_birth.required'    => 'Tanggal Lahir harus diisi',
                'department.required'       => 'Department harus diisi',
                'avatar.image'              => 'Avatar harus berupa gambar',
                'avatar.mimes'              => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.max'                => 'Ukuran Avatar maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $user = User::create(['email' => $request->email]);

                $structure = Structure::create([
                    'user_id' => $user->id ?? '',
                    'name' => $request->name ?? '',
                    'date_of_birth' => $request->date_of_birth ?? '',
                    'department' => $request->department ?? '',
                ]);

                $types = ['avatar'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $structure->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');

                        $structure->files()->where('type', $type)->delete();

                        $structure->files()->create([
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
                    'message' => 'Structure created successfully.',
                    'data' => $structure
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
        $structures = Structure::query()
            ->with([
                'user',
                'avatar',
            ])
            ->find($id);

        $data = [
            'data' => $structures,
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

        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'structure tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'email'                     => 'required|email|unique:users,email,' . $structure->user_id,
                'name'                      => 'required',
                'date_of_birth'             => 'required',
                'department'                => 'required',
                'avatar.*'                  => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'            => 'Email harus diisi',
                'email.email'               => 'Format email tidak valid',
                'email.unique'              => 'Email sudah digunakan',
                'name.required'             => 'Nama harus diisi',
                'date_of_birth.required'    => 'Tanggal Lahir harus diisi',
                'department.required'       => 'Department harus diisi',
                'avatar.*.image'            => 'Avatar harus berupa gambar',
                'avatar.*.mimes'            => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.*.max'              => 'Ukuran Avatar maksimal 2MB',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $structure->user()->update([
                    'email' => $request->email,
                    'password' => $request->password ? bcrypt($request->password) : $structure->user->password,
                ]);

                $structure->update([
                    'name'   => $request->name ?? '',
                    'date_of_birth' => $request->date_of_birth ?? '',
                    'department' => $request->department ?? '',
                ]);

                $types = ['avatar'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $structure->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $structure->files()->where('type', $type)->delete();

                        $structure->files()->create([
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
                    'message' => 'Structure updated successfully.',
                    'data' => $structure,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui structure.',
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
        $structure = Structure::withTrashed()->find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure tidak ditemukan'], 404);
        }

        $structure->delete();

        return response()->json([
            'message' => 'Structure berhasil dihapus.',
            'data' => new StructureResource($structure),
        ]);
    }

    public function active(Request $request, $id)
    {
        $structure = Structure::withTrashed()->find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure tidak ditemukan'], 404);
        }

        $structure->restore();

        return response()->json([
            'message' => 'Structure berhasil diaktifkan.',
            'data' => new StructureResource($structure),
        ]);
    }

    public function export(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $structuresQuery = Structure::query()
            ->select('user_id', 'code', 'name', 'department', 'deleted_at')
            ->with([
                'user:id,email',
            ])
            ->withTrashed();

        $structuresQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    });
            });
        });


        $structuresQuery->when($request->status, function ($query, $status) {
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
            $structuresQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $structuresQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $structuresQuery->orderBy($orderByColumn, $orderByDirection);

        $exportDir = 'StructureExport';
        if (!is_dir(Storage::disk('public')->path($exportDir))) {
            mkdir(Storage::disk('public')->path($exportDir), 0775, true);
            chmod(Storage::disk('public')->path($exportDir), 0775);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'A1' => 'Kode structure',
            'B1' => 'Nama',
            'C1' => 'Email',
            'D1' => 'Departemen',
            'E1' => 'Status'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $columnWidths = [
            'A' => 12,
            'B' => 25,
            'C' => 20,
            'D' => 30,
            'E' => 20
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $row = 2;
        $structuresQuery->chunk(1000, function ($structures) use ($sheet, &$row) {
            foreach ($structures as $structure) {
                // status diambil dari deleted_at (sesuai index)
                $statusText = isset($structure->deleted_at) ? 'Tidak Aktif' : 'Aktif';

                $sheet->setCellValue("A{$row}", $structure->code ?? '-');
                $sheet->setCellValue("B{$row}", $structure->name ?? '-');
                $sheet->setCellValue("C{$row}", $structure->user->email ?? '-');
                $sheet->setCellValue("D{$row}", $structure->department ?? '-');
                $sheet->setCellValue("E{$row}", $statusText);
                $row++;
            }
        });

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "structures_Export_{$timestamp}.xlsx";
        $filePath = Storage::disk('public')->path($exportDir . '/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $fileHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return response()->download($filePath, $fileName, $fileHeaders)->deleteFileAfterSend();
    }
}
