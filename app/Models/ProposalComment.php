<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProposalComment extends Model
{
    protected $fillable = ['proposal_id', 'author_name', 'body', 'is_sender', 'ip'];
    public function proposal() { return $this->belongsTo(Proposal::class); }
}