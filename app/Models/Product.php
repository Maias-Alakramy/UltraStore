<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $casts = [
        'exp_date' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'name',
        'price',
        'contact_info',
        'exp_date',
        'days1',"discount1",
        'days2',"discount2",
        'days3',"discount3",
        'img_url',
        'quantity',
        'category_id',
        'user_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function like()
    {
        return $this->hasMany(Like::class);
    }

}
