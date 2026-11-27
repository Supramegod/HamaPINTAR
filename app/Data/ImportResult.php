<?php

namespace App\Data;

final readonly class ImportResult
{
    /**
     * @param  list<int>  $invalidLines  nomor baris CSV (1-based, termasuk header) yang ditolak
     */
    public function __construct(
        public int $created,
        public int $duplicates,
        public array $invalidLines = [],
    ) {}

    public function message(): string
    {
        $message = "Proses selesai. {$this->created} data baru berhasil disimpan.";

        if ($this->duplicates > 0) {
            $message .= " ({$this->duplicates} data duplikat otomatis dilewati).";
        }

        if ($this->invalidLines !== []) {
            $shown = implode(', ', array_slice($this->invalidLines, 0, 10));
            $more = count($this->invalidLines) > 10 ? ', ...' : '';
            $message .= ' '.count($this->invalidLines)." baris tidak valid diabaikan (baris {$shown}{$more}).";
        }

        return $message;
    }
}
