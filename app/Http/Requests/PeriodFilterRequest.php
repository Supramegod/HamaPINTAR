<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filter periode (dan node, opsional) yang dipakai semua halaman monitoring.
 */
class PeriodFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'node' => ['nullable', Rule::in(array_keys(config('hamapintar.nodes')))],
        ];
    }

    public function attributes(): array
    {
        return ['start_date' => 'tanggal mulai', 'end_date' => 'tanggal akhir'];
    }

    /** Jika filter tidak valid, kembali ke halaman yang sama tanpa query string. */
    protected function getRedirectUrl(): string
    {
        return $this->url();
    }

    public function startDate(): CarbonImmutable
    {
        return $this->filled('start_date')
            ? CarbonImmutable::parse($this->validated('start_date'))
            : CarbonImmutable::today()->subDays(config('hamapintar.default_range_days'));
    }

    public function endDate(): CarbonImmutable
    {
        return $this->filled('end_date')
            ? CarbonImmutable::parse($this->validated('end_date'))
            : CarbonImmutable::today();
    }

    public function node(): ?string
    {
        return $this->validated('node');
    }
}
