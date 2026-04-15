<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tree extends Model {
    public $timestamps = false;
    protected $fillable = ['name', 'content', 'score', 'verdict', 'shard_id'];
}
