<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupJob extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'role_ids',
        'file_path',
        'file_expires_at',
        'progress',
        'report',
        'error_message',
        'include_images',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'role_ids' => 'array',
        'progress' => 'array',
        'report' => 'array',
        'include_images' => 'boolean',
        'file_expires_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExport(): bool
    {
        return $this->type === 'export';
    }

    public function isImport(): bool
    {
        return $this->type === 'import';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function hasDownload(): bool
    {
        return $this->isExport()
            && $this->isCompleted()
            && $this->file_path
            && $this->file_expires_at
            && $this->file_expires_at->isFuture();
    }
}
