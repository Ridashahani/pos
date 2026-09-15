<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'types'];

    protected $casts = ['types' => 'array'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}