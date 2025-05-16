<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use SoftDeletes, Blameable;

    protected $fillable = [
        'type',
        'name',
        'original_name',
        'extension',
        'path',
        'inspectionDate'
    ];

    protected $appends = [
        'url'
    ];

    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'fillable_type',
        'fillable_id',
        // 'inspectionDate'
    ];

    public static $file_directories = [
        'avatar' => 'avatar/',
        'kk' => 'kk/',
        'akta' => 'akta/',
        'report_card_grades' => 'report_card_grades/',
    ];

    public function getDirectory($type)
    {
        return self::$file_directories[$type];
    }

    public static function getFileName($type, $id, $file)
    {
        $extension = $file->getClientOriginalExtension();

        return $type . '-' . $id . '-' . Str::random(10) . '.' . $extension;
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function fileable()
    {
        return $this->morphTo();
    }
}
