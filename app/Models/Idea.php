<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Idea extends Model { protected $fillable = ['audience_id','submitted_by','title','description','problem','proposed_solution','target_user','monetization_idea','status']; public function audience() { return $this->belongsTo(Audience::class); } public function submitter() { return $this->belongsTo(User::class,'submitted_by'); } }
