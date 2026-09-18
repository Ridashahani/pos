<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;  

class Branch extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'address', 'phone', 'status'];

    public function scopeSearch(Builder $query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
            ->orwhere('address', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
