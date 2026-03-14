<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProposalView extends Model
{
    protected $fillable = ['proposal_id', 'ip', 'user_agent', 'viewed_at'];
    protected $casts = ['viewed_at' => 'datetime'];
    public function proposal() { return $this->belongsTo(Proposal::class); }
}