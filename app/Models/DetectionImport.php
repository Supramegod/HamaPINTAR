<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Riwayat berkas CSV yang pernah diunggah. `file_hash` unik → berkas yang sama ditolak.
 *
 * @property int $id
 * @property string $original_name
 * @property int $file_size
 * @property string $file_hash
 * @property int $created_count
 * @property int $duplicate_count
 * @property int $invalid_count
 * @property Carbon $created_at
 * @property-read User|null $user
 */
class DetectionImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'original_name', 'file_size', 'file_hash',
        'created_count', 'duplicate_count', 'invalid_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Nama pengunggah, aman jika akunnya sudah dihapus. */
    public function uploaderName(): string
    {
        return $this->user?->name ?? 'pengguna yang telah dihapus';
    }

    /** Ukuran berkas, mis. "12,4 KB" (tanpa ekstensi intl). */
    public function sizeForHumans(): string
    {
        $units = ['B', 'KB', 'MB'];
        $size = (float) $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return number_format($size, $i === 0 ? 0 : 1, ',', '.').' '.$units[$i];
    }

    public function totalRows(): int
    {
        return $this->created_count + $this->duplicate_count + $this->invalid_count;
    }
}
