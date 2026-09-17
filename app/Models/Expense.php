<?php

namespace App\Models;

use App\Models\Branch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'category', 'amount', 'date', 'description'];

    public function scopeSearch(Builder $query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('category', 'like', "%{$search}%")
                ->orWhereHas('branch', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
