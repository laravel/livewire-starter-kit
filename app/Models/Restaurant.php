<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Item;

class Restaurant extends Model
{
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function items()
    {
        return $this->hasManyThrough(
            Item::class,
            Category::class
        );
    }
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

}
