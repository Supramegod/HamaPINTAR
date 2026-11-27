<?php

namespace App\Http\Requests;

use App\Models\DetectionImport;
use App\Services\Detection\CsvFingerprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportDetectionCsvRequest extends FormRequest
{
    private ?string $fingerprint = null;

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:'.config('hamapintar.import.max_file_kb')],
        ];
    }

    public function attributes(): array
    {
        return ['csv_file' => 'berkas CSV'];
    }

    /** Tolak berkas yang isinya identik dengan berkas yang pernah diunggah. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('csv_file')) {
                    return;
                }

                $previous = DetectionImport::with('user')->firstWhere('file_hash', $this->fingerprint());

                if ($previous) {
                    $validator->errors()->add('csv_file', sprintf(
                        'Berkas ini sudah pernah diunggah pada %s oleh %s (nama berkas "%s"). Tidak ada data yang diproses.',
                        $previous->created_at->translatedFormat('d M Y H:i'),
                        $previous->uploaderName(),
                        $previous->original_name,
                    ));
                }
            },
        ];
    }

    public function fingerprint(): string
    {
        return $this->fingerprint ??= CsvFingerprint::of($this->file('csv_file')->getRealPath());
    }
}
