<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_user_id', 'action', 'subject_type', 'subject_id', 'meta', 'ip',
    ];

    protected $casts = ['meta' => 'array'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /** Log a new activity */
    public static function log(string $action, mixed $subject = null, array $meta = []): void
    {
        $adminId = auth('admin')->id();
        if (!$adminId) return;

        static::create([
            'admin_user_id' => $adminId,
            'action'        => $action,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject?->id,
            'meta'          => $meta,
            'ip'            => request()->ip(),
        ]);
    }
}