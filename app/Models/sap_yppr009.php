<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class sap_yppr009 extends Model
{
    protected $fillable = [
        'LGORT',
        'DISPO',
        'AUFNR',
        'CHARG',
        'MATNR',
        'MAKTX',
        'MAT_KDAUF',
        'MAT_KDPOS',
        'PSMNG',
        'MENGE',
        'MENGEX',
        'WEMNG',
        'MEINS',
        'BUDAT_MKPF',
        'NODAY',
        'NETPR',
        'VALUS',
        'VALUSX',
    ];

    protected $casts = [
        'BUDAT_MKPF' => 'date',
        'PSMNG' => 'decimal:3',
        'MENGE' => 'decimal:3',
        'MENGEX' => 'decimal:3',
        'WEMNG' => 'decimal:3',
        'NODAY' => 'integer',
        'NETPR' => 'decimal:3',
        'VALUS' => 'decimal:3',
        'VALUSX' => 'decimal:3',
    ];

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('BUDAT_MKPF', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by DISPO
     */
    public function scopeByDispo($query, $dispo)
    {
        if (is_array($dispo)) {
            return $query->whereIn('DISPO', $dispo);
        }
        return $query->where('DISPO', $dispo);
    }

    /**
     * Scope to filter by material number
     */
    public function scopeByMaterial($query, $matnr)
    {
        return $query->where('MATNR', $matnr);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->BUDAT_MKPF ? $this->BUDAT_MKPF->format('d/m/Y') : null;
    }

    /**
     * Get total value
     */
    public function getTotalValueAttribute()
    {
        return $this->VALUS + $this->VALUSX;
    }

    /**
     * Delete records by date range
     */
    public static function deleteByDateRange($startDate, $endDate)
    {
        return static::whereBetween('BUDAT_MKPF', [$startDate, $endDate])->delete();
    }

    /**
     * Bulk insert records
     */
    public static function bulkInsert(array $records)
    {
        // Add timestamps to all records
        $timestamp = now();
        $records = array_map(function ($record) use ($timestamp) {
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
            return $record;
        }, $records);

        return static::insert($records);
    }
}
