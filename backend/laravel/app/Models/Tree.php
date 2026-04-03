<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tree extends Model {
    protected $fillable = ['name', 'species', 'health_score'];
}
