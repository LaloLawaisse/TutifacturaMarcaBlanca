<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiales';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * Normaliza el atributo ID al hidratar desde la DB.
     * En algunas configuraciones de MySQL/PDO, la columna 'ID' (mayúscula)
     * se retorna como 'id' (minúscula), dejando getKey() en null.
     */
    public static function newFromBuilder($attributes = [], $connection = null)
    {
        $attrs = (array) $attributes;
        if (array_key_exists('id', $attrs) && !array_key_exists('ID', $attrs)) {
            $attrs['ID'] = $attrs['id'];
        }
        return parent::newFromBuilder($attrs, $connection);
    }

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
