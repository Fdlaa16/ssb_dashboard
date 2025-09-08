<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerResources;
use App\Http\Resources\SportResource;
use App\Mail\ApprovePlayerMail;
use App\Mail\RejectPlayerMail;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\File;
use App\Models\Player;
use App\Models\Sport;
use App\Models\SportPlayer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlayerController extends Controller
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

        $playersQuery = Player::query()
            ->with([
                'user',
                'sports' => function ($query) {
                    $query->whereNull('sports.deleted_at');
                },
            ])
            ->withTrashed();

        $playersQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%')
                    ->orWhere('height', 'like', '%' . $request->search . '%')
                    ->orWhere('weight', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    });
            });
        });

        $playersQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_confirm':
                    $query->where('status', 0);
                    break;
                case 'active':
                    $query->where('status', 1);
                    break;
                case 'in_active':
                    $query->where('status', 2);
                    break;
                case 'all':
                default:
                    break;
            }
        });

        $playersQuery->when($request->club_id, function ($query) use ($request) {
            $query->whereHas('clubs', function ($q) use ($request) {
                $q->where('clubs.id', $request->club_id);
            });
        });

        $playersQuery->when($request->sport_id, function ($query) use ($request) {
            $query->whereHas('sports', function ($q) use ($request) {
                $q->where('id', $request->sport_id);
            });
        });

        if ($request->filled('from_date')) {
            $playersQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $playersQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $playersQuery->orderBy($orderByColumn, $orderByDirection);

        $players = $playersQuery->get();

        $statusCounts = DB::table('players')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAll = $statusCounts->sum();

        $responseTotals = [
            'all' => $totalAll,
            'active' => $statusCounts[1] ?? 0,
            'in_confirm' => $statusCounts[0] ?? 0,
            'in_active' => $statusCounts[2] ?? 0,
        ];

        return response()->json([
            'data' => $players,
            'totals' => $responseTotals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $sports = Sport::select('id', 'code', 'name')->get();

        $data = [
            'sports' => SportResource::collection($sports),
        ];

        return $data;
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
                'email'     => 'required|email|unique:users,email',
                'nisn'      => 'required|unique:players,nisn',
                'phone'      => 'required|unique:players,phone',
                'name'      => 'required',
                'height'    => 'required',
                'weight'    => 'required',
                // 'club_id'   => 'required',
                'category'  => 'required',
                'position'  => 'required',
                'avatar' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'family_card' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'report_grades' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'birth_certificate' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'     => 'Email harus diisi',
                'email.email'        => 'Format email tidak valid',
                'email.unique'       => 'Email sudah digunakan',
                'nisn.required'      => 'NISN harus diisi',
                'nisn.unique'        => 'NISN sudah digunakan',
                'phone.required'     => 'Nomor Telepon harus diisi',
                'phone.unique'       => 'Nomor Telepon sudah digunakan',
                'name.required'      => 'Nama harus diisi',
                'height.required'    => 'Tinggi badan harus diisi',
                'weight.required'    => 'Berat badan harus diisi',
                // 'club_id.required'   => 'Club harus diisi',
                // 'club_id.exists'     => 'Club tidak ditemukan',
                'category.required'  => 'Kategori harus diisi',
                'position.required'  => 'Posisi harus diisi',
                'avatar.image' => 'Avatar harus berupa gambar',
                'avatar.mimes' => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.max' => 'Ukuran Avatar maksimal 2MB',
                'family_card.image' => 'Kartu Keluarga harus berupa gambar',
                'family_card.mimes' => 'Format Kartu Keluarga harus jpeg, png, jpg, atau bmp',
                'family_card.max' => 'Ukuran Kartu Keluarga maksimal 2MB',
                'report_grades.image' => 'Nilai Rapot harus berupa gambar',
                'report_grades.mimes' => 'Format Nilai Rapot harus jpeg, png, jpg, atau bmp',
                'report_grades.max' => 'Ukuran Nilai Rapot maksimal 2MB',
                'birth_certificate.image' => 'Akte Kelahiran harus berupa gambar',
                'birth_certificate.mimes' => 'Format Akte Kelahiran harus jpeg, png, jpg, atau bmp',
                'birth_certificate.max' => 'Ukuran Akte Kelahiran maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $user = User::create(['email' => $request->email]);

                $player = Player::create([
                    'user_id' => $user->id ?? '',
                    'nisn' => $request->nisn ?? '',
                    'phone' => $request->phone ?? '',
                    'name' => $request->name ?? '',
                    'height' => $request->height ?? '',
                    'weight' => $request->weight ?? '',
                    'position' => $request->position ?? '',
                    'back_number' => $request->back_number ?? '',
                    'category' => $request->category ?? '',
                ]);

                $types = ['avatar', 'family_card', 'report_grades', 'birth_certificate'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $player->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');

                        $player->files()->where('type', $type)->delete();

                        $player->files()->create([
                            'type' => $type,
                            'name' => $fileName,
                            'original_name' => $file->getClientOriginalName(),
                            'extension' => $file->getClientOriginalExtension(),
                            'path' => "$fileDir$fileName",
                        ]);
                    }
                }

                // ClubPlayer::create([
                //     'club_id' => $request->club_id,
                //     'player_id' => $player->id,
                //     'position' => $request->position,
                //     'back_number' => $request->back_number,
                //     'category' => $request->category,
                // ]);

                DB::commit();

                return response()->json([
                    'message' => 'Pemain created successfully.',
                    'data' => $player
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
        $players = Player::query()
            ->with([
                'user',
                // 'clubPlayers.club.profile_club',
                'avatar',
                'birth_certificate',
                'family_card',
                'report_grades',
            ])
            ->find($id);

        $data = [
            'data' => $players,
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

        $player = Player::find($id);

        if (!$player) {
            return response()->json(['message' => 'Pemain tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'email'     => 'required|email|unique:users,email,' . $player->user_id,
                'nisn'      => 'required|unique:players,nisn,' . $player->id,
                'phone'      => 'required|unique:players,phone,' . $player->id,
                'name'      => 'required',
                'height'    => 'required',
                'weight'    => 'required',
                // 'club_id'   => 'required',
                'category'  => 'required',
                'position'  => 'required',
                'avatar.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'family_card.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'report_grades.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'birth_certificate.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'     => 'Email harus diisi',
                'email.email'        => 'Format email tidak valid',
                'email.unique'       => 'Email sudah digunakan',
                'nisn.required'      => 'NISN harus diisi',
                'nisn.unique'        => 'NISN sudah digunakan',
                'phone.required'     => 'Nomor Telepon harus diisi',
                'phone.unique'       => 'Nomor Telepon sudah digunakan',
                'name.required'      => 'Nama harus diisi',
                'height.required'    => 'Tinggi badan harus diisi',
                'weight.required'    => 'Berat badan harus diisi',
                // 'club_id.required'   => 'Club harus diisi',
                // 'club_id.exists'     => 'Club tidak ditemukan',
                'category.required'  => 'Kategori harus diisi',
                'position.required'  => 'Posisi harus diisi',
                'avatar.*.image' => 'Avatar harus berupa gambar',
                'avatar.*.mimes' => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.*.max' => 'Ukuran Avatar maksimal 2MB',
                'family_card.*.image' => 'Kartu Keluarga harus berupa gambar',
                'family_card.*.mimes' => 'Format Kartu Keluarga harus jpeg, png, jpg, atau bmp',
                'family_card.*.max' => 'Ukuran Kartu Keluarga maksimal 2MB',
                'report_grades.*.image' => 'Nilai Rapot harus berupa gambar',
                'report_grades.*.mimes' => 'Format Nilai Rapot harus jpeg, png, jpg, atau bmp',
                'report_grades.*.max' => 'Ukuran Nilai Rapot maksimal 2MB',
                'birth_certificate.*.image' => 'Akte Kelahiran harus berupa gambar',
                'birth_certificate.*.mimes' => 'Format Akte Kelahiran harus jpeg, png, jpg, atau bmp',
                'birth_certificate.*.max' => 'Ukuran Akte Kelahiran maksimal 2MB',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $player->user()->update([
                    'email' => $request->email,
                ]);

                $player->update([
                    'nisn'   => $request->nisn ?? '',
                    'phone'   => $request->phone ?? '',
                    'name'   => $request->name ?? '',
                    'height' => $request->height ?? '',
                    'weight' => $request->weight ?? '',
                    'position' => $request->position ?? '',
                    'back_number' => $request->back_number ?? '',
                    'category' => $request->category ?? '',
                ]);

                $types = ['avatar', 'family_card', 'report_grades', 'birth_certificate'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $player->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $player->files()->where('type', $type)->delete();

                        $player->files()->create([
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
                    'message' => 'Pemain updated successfully.',
                    'data' => $player,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui pemain.',
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
        $player = Player::withTrashed()->find($id);

        if (!$player) {
            return response()->json(['message' => 'Player tidak ditemukan'], 404);
        }

        $player->delete();

        return response()->json([
            'message' => 'Pemain berhasil dihapus.',
            'data' => new PlayerResource($player),
        ]);
    }

    public function activate(Request $request, $id)
    {
        $player = Player::withTrashed()->find($id);

        if (!$player) {
            return response()->json(['message' => 'Player tidak ditemukan'], 404);
        }

        $player->restore();

        return response()->json([
            'message' => 'Player berhasil diaktifkan.',
            'data' => new PlayerResource($player),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $player = Player::with('user')->withTrashed()->find($id);

        if (!$player) {
            return response()->json(['message' => 'Pemain tidak ditemukan'], 404);
        }

        $player->status = 1;
        $player->save();

        // Kirim email notifikasi registrasi berhasil
        try {
            Mail::to($player->user->email)->send(new ApprovePlayerMail($player, $player->user));
        } catch (\Exception $mailException) {
            // Log error email tapi tetap lanjutkan proses
            \Log::error('Failed to send approve email: ' . $mailException->getMessage());
        }

        return response()->json([
            'message' => 'Pemain berhasil disetujui.',
            'data' => new PlayerResource($player),
        ]);
    }

    public function reject(Request $request, $id)
    {
        $player = Player::with('user')->withTrashed()->find($id);

        if (!$player) {
            return response()->json(['message' => 'Pemain tidak ditemukan'], 404);
        }

        $player->status = 2;
        $player->save();

        // Kirim email notifikasi registrasi berhasil
        try {
            Mail::to($player->user->email)->send(new RejectPlayerMail($player, $player->user));
        } catch (\Exception $mailException) {
            // Log error email tapi tetap lanjutkan proses
            \Log::error('Failed to send reject email: ' . $mailException->getMessage());
        }

        return response()->json([
            'message' => 'Pemain berhasil ditolak.',
            'data' => new PlayerResource($player),
        ]);
    }

    public function export(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $playersQuery = Player::query()
            ->select('id', 'user_id', 'code', 'name', 'nisn', 'height', 'weight', 'position', 'category', 'status', 'deleted_at')
            ->with([
                'user:id,email'
            ])
            ->withTrashed();

        // 🔎 Filter search
        $playersQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%')
                    ->orWhere('height', 'like', '%' . $request->search . '%')
                    ->orWhere('weight', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    });
            });
        });

        // 🔎 Filter status
        $playersQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_confirm':
                    $query->where('status', 0);
                    break;
                case 'active':
                    $query->where('status', 1);
                    break;
                case 'in_active':
                    $query->where('status', 2);
                    break;
                case 'all':
                default:
                    break;
            }
        });

        // 🔎 Filter tanggal
        if ($request->filled('from_date')) {
            $playersQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $playersQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $playersQuery->orderBy($orderByColumn, $orderByDirection);

        // 🔎 Buat folder kalau belum ada
        $exportDir = 'PlayersExport';
        if (!is_dir(Storage::disk('public')->path($exportDir))) {
            mkdir(Storage::disk('public')->path($exportDir), 0775, true);
            chmod(Storage::disk('public')->path($exportDir), 0775);
        }

        // 🔎 Setup Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'A1' => 'Kode Pemain',
            'B1' => 'Nama',
            'C1' => 'NISN',
            'D1' => 'Email',
            'E1' => 'Tinggi (cm)',
            'F1' => 'Berat (kg)',
            'G1' => 'Posisi',
            'H1' => 'Kategori',
            'I1' => 'Status',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $columnWidths = [
            'A' => 12,
            'B' => 25,
            'C' => 20,
            'D' => 30,
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 15,
            'I' => 25,
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // 🔎 Isi data
        $row = 2;
        $playersQuery->chunk(1000, function ($players) use ($sheet, &$row) {
            foreach ($players as $player) {
                if ($player->deleted_at) {
                    $statusText = 'Non Aktif'; // soft delete
                } else {
                    $statusText = match ($player->status) {
                        0 => 'Menunggu Konfirmasi',
                        1 => 'Pemain Aktif Pada Halaman',
                        2 => 'Pemain Aktif',
                        3 => 'Perlu Perbaikan',
                        default => 'Unknown',
                    };
                }

                $ageText = match ($player->category) {
                    'u9' => 'U-9',
                    'u10' => 'U-10',
                    'u11' => 'U-11',
                    'u12' => 'U-12',
                    'u13' => 'U-13',
                    'u14' => 'U-14',
                    'u15' => 'U-15',
                    default => 'Unknown',
                };

                $sheet->setCellValue("A{$row}", $player->code ?? '-');
                $sheet->setCellValue("B{$row}", $player->name ?? '-');
                $sheet->setCellValue("C{$row}", $player->nisn ?? '-');
                $sheet->setCellValue("D{$row}", $player->user->email ?? '-');
                $sheet->setCellValue("E{$row}", $player->height ?? '-');
                $sheet->setCellValue("F{$row}", $player->weight ?? '-');
                $sheet->setCellValue("G{$row}", $player->position ?? '-');
                $sheet->setCellValue("H{$row}", $ageText);
                $sheet->setCellValue("I{$row}", $statusText);
                $row++;
            }
        });

        // 🔎 Simpan file
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "Players_Export_{$timestamp}.xlsx";
        $filePath = Storage::disk('public')->path($exportDir . '/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $fileHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return response()->download($filePath, $fileName, $fileHeaders)->deleteFileAfterSend();
    }

    public function downloadBiodata($id)
    {
        try {
            $player = Player::query()
                ->with([
                    'user',
                    'avatar',
                    'birth_certificate',
                    'family_card',
                    'report_grades',
                ])
                ->find($id);

            if (!$player) {
                return response()->json(['error' => 'Pemain tidak ditemukan'], 404);
            }

            // Load logo dari backend dan ubah jadi base64
            $path = public_path('storage/logo/LOGOSSB.png');
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);

            // Avatar base64
            $avatarBase64 = null;
            if ($player->avatar && $player->avatar->path) {
                $avatarPath = public_path('storage/' . $player->avatar->path);
                if (file_exists($avatarPath)) {
                    $avatarType = pathinfo($avatarPath, PATHINFO_EXTENSION);
                    $avatarData = file_get_contents($avatarPath);
                    $avatarBase64 = 'data:image/' . $avatarType . ';base64,' . base64_encode($avatarData);
                }
            }

            $attachments = [];

            if ($player->family_card && $player->family_card->path) {
                $familyCardPath = public_path('storage/' . $player->family_card->path);
                if (file_exists($familyCardPath)) {
                    $familyCardType = pathinfo($familyCardPath, PATHINFO_EXTENSION);
                    $familyCardData = file_get_contents($familyCardPath);
                    $attachments['family_card'] = [
                        'title' => 'Kartu Keluarga',
                        'data' => 'data:image/' . $familyCardType . ';base64,' . base64_encode($familyCardData)
                    ];
                }
            }

            if ($player->report_grades && $player->report_grades->path) {
                $reportGradesPath = public_path('storage/' . $player->report_grades->path);
                if (file_exists($reportGradesPath)) {
                    $reportGradesType = pathinfo($reportGradesPath, PATHINFO_EXTENSION);
                    $reportGradesData = file_get_contents($reportGradesPath);
                    $attachments['report_grades'] = [
                        'title' => 'Rapor',
                        'data' => 'data:image/' . $reportGradesType . ';base64,' . base64_encode($reportGradesData)
                    ];
                }
            }

            if ($player->birth_certificate && $player->birth_certificate->path) {
                $birthCertificatePath = public_path('storage/' . $player->birth_certificate->path);
                if (file_exists($birthCertificatePath)) {
                    $birthCertificateType = pathinfo($birthCertificatePath, PATHINFO_EXTENSION);
                    $birthCertificateData = file_get_contents($birthCertificatePath);
                    $attachments['birth_certificate'] = [
                        'title' => 'Akte Kelahiran',
                        'data' => 'data:image/' . $birthCertificateType . ';base64,' . base64_encode($birthCertificateData)
                    ];
                }
            }

            $ageText = match ($player->category) {
                'u9' => 'U-9',
                'u10' => 'U-10',
                'u11' => 'U-11',
                'u12' => 'U-12',
                'u13' => 'U-13',
                'u14' => 'U-14',
                'u15' => 'U-15',
                default => 'Unknown',
            };

            $positionText = match ($player->position) {
                'front' => 'Depan',
                'center' => 'Tengah',
                'back' => 'Belakang',
                'gk' => 'GK',
                default => 'Unknown',
            };

            // Data untuk PDF
            $data = [
                'player' => $player,
                'title' => 'Biodata Pemain - ' . $player->name,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'logo' => $base64Logo,
                'avatar' => $avatarBase64,
                'positionText' => $positionText,
                'ageText' => $ageText,
                'attachments' => $attachments
            ];

            $pdf = Pdf::loadView('pdf.biodata', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 96,
                    'defaultFont' => 'Arial',
                ]);

            $filename = 'biodata_' . $player->code . '_' . date('YmdHis') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Error downloading biodata: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengunduh biodata: ' . $e->getMessage()
            ], 500);
        }
    }
}
