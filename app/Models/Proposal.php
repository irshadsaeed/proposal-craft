<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProposalSection;
use App\Models\ProposalComment;
use App\Models\ProposalView;
use App\Models\ProposalTrackingEvent;
use App\Models\ClientUser;


class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'client',
        'status',
        'amount',
        'views_count',
        'avg_time_open',
        'last_seen',
        'sent_at',
        'token',
        'first_viewed_at',
        'accepted_by',
        'accepted_email',
        'accepted_at',
        'accepted_ip',
        'signature_path',
        'declined_at',
        'decline_reason',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'accepted_at'     => 'datetime',
        'declined_at'     => 'datetime',
        'first_viewed_at' => 'datetime',
        'amount'          => 'decimal:2',
    ];

    public function sender()
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function sections()
    {
        return $this->hasMany(ProposalSection::class);
    }

    public function comments()
    {
        return $this->hasMany(ProposalComment::class);
    }

    public function proposalViews()
    {
        return $this->hasMany(ProposalView::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(ProposalTrackingEvent::class);
    }

    public function client()
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }
}
