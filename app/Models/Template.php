<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientUser;

class Template extends Model
{
    use HasFactory;

    /* ── Fillable ────────────────────────────────────────────────────── */
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'description',
        'color',
        'content',       /* MEDIUMTEXT — JSON array of blocks */
        'thumbnail',     /* nullable string — preview image URL */
        'is_active',     /* boolean */
        'blocks_count',  /* unsigned smallint — cached block count */
    ];

    /* ── Casts ───────────────────────────────────────────────────────── */
    protected $casts = [
        'is_active'    => 'boolean',
        'blocks_count' => 'integer',
        /* NOTE: do NOT cast 'content' to 'array' here.
           The JS editor receives it as a raw JSON string via @json()
           in the Blade template. Casting to array would double-encode
           it when accessed via $template->content in @json(). */
    ];

    /* ── Relationships ───────────────────────────────────────────────── */
    public function user()
    {
        return $this->belongsTo(ClientUser::class);
    }

    /* ── Accessors / Helpers ─────────────────────────────────────────── */

    /**
     * Decode the content JSON string into a PHP array safely.
     * Returns an empty array if content is null or malformed.
     */
    public function getBlocksAttribute(): array
    {
        if (empty($this->content)) {
            return [];
        }

        $decoded = json_decode($this->content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * How many blocks this template has (decoded live if cache is 0).
     */
    public function blockCount(): int
    {
        if ($this->blocks_count > 0) {
            return $this->blocks_count;
        }

        return count($this->blocks);
    }

    /**
     * Sync the cached blocks_count with the actual decoded content.
     * Called automatically by TemplatesController::autosave().
     */
    public function syncBlocksCount(): void
    {
        $count = count($this->blocks);

        if ($this->blocks_count !== $count) {
            $this->timestamps = false; /* don't bump updated_at for cache sync */
            $this->update(['blocks_count' => $count]);
            $this->timestamps = true;
        }
    }
}