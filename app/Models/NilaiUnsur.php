<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class NilaiUnsur extends Model
{
    use HasFactory;

    /** Default to the most recent known table. */
    public $table = 'survey_responses';

    /**
     * Return a query-builder / model scoped to a specific year's table.
     *
     * Usage:
     *   NilaiUnsur::forYear(2023)->where(...)->get();
     *
     * Falls back to the most recent available year-table when the requested
     * year does not have its own table in the database.
     */
    public static function forYear(int $year): static
    {
        $instance = new static();

        $candidate = (string) $year;
        $instance->table = Schema::hasTable($candidate)
            ? $candidate
            : static::latestAvailableTable();

        return $instance;
    }

    /**
     * Find the most-recent year that actually has a table in the database.
     */
    public static function latestAvailableTable(): string
    {
        foreach (range((int) date('Y'), 2023) as $y) {
            if (Schema::hasTable((string) $y)) {
                return (string) $y;
            }
        }

        return '2024'; // hard fallback — table must exist before the app is used
    }

    /** Convenience: scope the model to the current calendar year. */
    public static function current(): static
    {
        return static::forYear((int) date('Y'));
    }

    protected $fillable = [
            'client_uuid',
            'jenisPelayanan',
            'tahun',
            'nama' ,
            'alamat' ,
            'pekerjaan' ,
            'jenkel' ,
            'usia' ,
            'nohp' ,
            'pendidikan' ,
            'nik' ,
            'u1' ,
            'u2' ,
            'u3' ,
            'u4' ,
            'u5' ,
            'u6' ,
            'u7' ,
            'u8' ,
            'u9' ,
            'saran' ,
            'id_sub_jenis' ,
            'responden_uuid',
            'jenis_responden',
            'desa_id',
            'pilar_id',
            'program_id',
            'rincian_id',
            'perusahaan_jawaban',
            'perusahaan_dinilai',
            'sumber_atribusi',
            'perusahaan_opsi',
    ];
}
