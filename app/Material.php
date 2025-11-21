<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiales';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'nombre',
        'precio',
        'unidades_en_stock',
        'productos_linkeados',
    ];

    protected $casts = [
        'productos_linkeados' => 'array',
        'precio' => 'float',
        'unidades_en_stock' => 'int',
    ];

    public function getLinkedProductsAttribute()
    {
        $ids = $this->productos_linkeados ?: [];
        if (!is_array($ids) || empty($ids)) {
            return collect();
        }
        return Product::whereIn('id', $ids)->get();
    }

    public function getRouteKeyName()
    {
        return 'ID';
    }
}
