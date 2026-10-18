<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuatPesananRequest;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function store(BuatPesananRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'PSN-' . Str::upper(Str::random(8)),
            'nama_pelanggan' => $data['nama_pelanggan'],
            'sku_produk' => $data['sku_produk'],
            'jumlah' => $data['jumlah'],
            'status' => 'menunggu',
        ]);

        return response()
            ->json([
                'data' => [
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'nama_pelanggan' => $pesanan->nama_pelanggan,
                    'sku_produk' => $pesanan->sku_produk,
                    'jumlah' => $pesanan->jumlah,
                    'status' => $pesanan->status,
                ],
            ], 201)
            ->header(
                'Location',
                url('/api/v1/pesanan/' . $pesanan->kode_pesanan)
            );
    }
    
    public function show(string $kode): JsonResponse
    {
    	$pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

	    return response()->json([
        	'data' => [
            		'kode_pesanan' => $pesanan->kode_pesanan,
            		'nama_pelanggan' => $pesanan->nama_pelanggan,
            		'sku_produk' => $pesanan->sku_produk,
            		'jumlah' => $pesanan->jumlah,
           		 'status' => $pesanan->status,
	        ],
   	 ]);
    }
    
    public function konfirmasi(string $kode): JsonResponse
    {
    	$pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

    	if ($pesanan->status !== 'menunggu') {
        	return response()->json([
            		'type' => 'https://layanan-pemesanan.test/problem/konflik-status',
            		'title' => 'Status pesanan tidak sesuai',
            		'status' => 409,
            		'detail' => 'Pesanan yang tidak berstatus menunggu tidak dapat dikonfirmasi.',
           		'instance' => '/' . request()->path(),
        	], 409, [
            		'Content-Type' => 'application/problem+json',
        	]);
    	}

    	$pesanan->update([
        		'status' => 'dikonfirmasi',
    	]);

    		return response()->json([
        		'data' => [
            		'kode_pesanan' => $pesanan->kode_pesanan,
          		'nama_pelanggan' => $pesanan->nama_pelanggan,
            		'sku_produk' => $pesanan->sku_produk,
            		'jumlah' => $pesanan->jumlah,
            		'status' => $pesanan->status,
        	],
    	]);
    }

    public function batal(string $kode): JsonResponse
    {
    	$pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

	    if ($pesanan->status !== 'menunggu') {
	        return response()->json([
        	    'type' => 'https://layanan-pemesanan.test/problem/konflik-status',
	            'title' => 'Status pesanan tidak sesuai',
        	    'status' => 409,
        	    'detail' => 'Pesanan yang tidak berstatus menunggu tidak dapat dibatalkan.',
     		    'instance' => '/' . request()->path(),
    	    ], 409, [
        	    'Content-Type' => 'application/problem+json',
    	    ]);
    	}

    	$pesanan->update([
        	'status' => 'dibatalkan',
    	]);

  	  	return response()->json([
  			'data' => [
  	          	'kode_pesanan' => $pesanan->kode_pesanan,
      		   	'nama_pelanggan' => $pesanan->nama_pelanggan,
        	   	'sku_produk' => $pesanan->sku_produk,
        		'jumlah' => $pesanan->jumlah,
           		'status' => $pesanan->status,
       		 ],
   	 ]);
    }
}
