<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalTrackingEvent extends Model
{
    protected $fillable = ['proposal_id', 'event_type', 'section_id', 'value', 'meta', 'ip', 'tracked_at'];
    protected $casts = ['tracked_at' => 'datetime'];
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
