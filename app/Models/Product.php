<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'user_id',
        'category_id'
    ];

    /**
     * Relationship to Users
     */
    public function users(){

        // SELECT * 
        // FROM products
        // INNER JOIN users
        // ON products.user_id = users.id;

        return $this->belongsTo('App\Models\User','user_id')->select(['id','fullname','avatar']); 
    }

    /**
     * Relationship to Categories
     */
    public function categories(){

        // SELECT * 
        // FROM products
        // INNER JOIN categories
        // ON products.category_id = categories.id;

        return $this->belongsTo('App\Models\Category','category_id')->select(['id','name','status']);
    }
}
