<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuatPesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => ['required', 'string', 'max:120'],
            'sku_produk' => ['required', 'string'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ];
    }
}
