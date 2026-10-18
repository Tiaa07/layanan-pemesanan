<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dapat_membuat_pesanan_baru(): void
    {
        $response = $this->postJson('/api/v1/pesanan', [
            'nama_pelanggan' => 'Tia Anggraeni',
            'sku_produk' => 'KRD-001',
            'jumlah' => 2,
        ]);

        $response
            ->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.nama_pelanggan', 'Tia Anggraeni')
            ->assertJsonPath('data.sku_produk', 'KRD-001')
            ->assertJsonPath('data.jumlah', 2)
            ->assertJsonPath('data.status', 'menunggu')
            ->assertJsonStructure([
                'data' => [
                    'kode_pesanan',
                    'nama_pelanggan',
                    'sku_produk',
                    'jumlah',
                    'status',
                ],
            ]);
    }
    
    public function test_gagal_membuat_pesanan_jika_jumlah_kurang_dari_satu(): void
    {
    	$response = $this->postJson('/api/v1/pesanan', [
        	'nama_pelanggan' => 'Tia Anggraeni',
        	'sku_produk' => 'KRD-001',
        	'jumlah' => 0,
    	]);

    	$response
        	->assertStatus(422)
        	->assertHeader('Content-Type', 'application/problem+json')
        	->assertJsonPath(
            		'type',
            		'https://layanan-pemesanan.test/problem/validasi-gagal'
        	)
        	->assertJsonPath('title', 'Data permintaan tidak valid')
        	->assertJsonPath('status', 422)
        	->assertJsonPath('instance', '/api/v1/pesanan')
       		->assertJsonStructure([
            		'type',
            		'title',
            		'status',
            		'detail',
            		'instance',
            		'errors' => [
                	'jumlah',
            	],
        	]);
	}
    
    public function test_dapat_melihat_detail_pesanan(): void
    {
    	$pesanan = \App\Models\Pesanan::create([
        	'kode_pesanan' => 'PSN-DETAIL01',
        	'nama_pelanggan' => 'Tia Anggraeni',
        	'sku_produk' => 'KRD-001',
       	 	'jumlah' => 2,
        	'status' => 'menunggu',
    	]);

   	$response = $this->getJson(
        	'/api/v1/pesanan/' . $pesanan->kode_pesanan
    	);

    	$response
        	->assertStatus(200)
        	->assertJsonPath('data.kode_pesanan', 'PSN-DETAIL01')
     	   	->assertJsonPath('data.nama_pelanggan', 'Tia Anggraeni')
       	 	->assertJsonPath('data.sku_produk', 'KRD-001')
        	->assertJsonPath('data.jumlah', 2)
        	->assertJsonPath('data.status', 'menunggu');
    }

    public function test_gagal_melihat_pesanan_yang_tidak_ada(): void
    {
 	   $response = $this->getJson('/api/v1/pesanan/PSN-TIDAKADA');

    	   $response
       		->assertStatus(404)
        	->assertHeader('Content-Type', 'application/problem+json')
        	->assertJsonPath(
            		'type',
          		'https://layanan-pemesanan.test/problem/tidak-ditemukan'
        	)
       		->assertJsonPath('title', 'Pesanan tidak ditemukan')
        	->assertJsonPath('status', 404)
        	->assertJsonPath('instance', '/api/v1/pesanan/PSN-TIDAKADA');
    }

    public function test_dapat_mengonfirmasi_pesanan_yang_masih_menunggu(): void
    {
    	$pesanan = \App\Models\Pesanan::create([
        	'kode_pesanan' => 'PSN-KONFIRM01',
        	'nama_pelanggan' => 'Tia Anggraeni',
        	'sku_produk' => 'KRD-001',
        	'jumlah' => 2,
      		'status' => 'menunggu',
    	]);

    	$response = $this->postJson(
        	'/api/v1/pesanan/' . $pesanan->kode_pesanan . '/konfirmasi'
    	);

    	$response
        	->assertStatus(200)
        	->assertJsonPath('data.kode_pesanan', 'PSN-KONFIRM01')
      		->assertJsonPath('data.status', 'dikonfirmasi');

  	$this->assertDatabaseHas('pesanans', [
        	'kode_pesanan' => 'PSN-KONFIRM01',
        	'status' => 'dikonfirmasi',
    	]);
    }

    public function test_gagal_mengonfirmasi_pesanan_yang_sudah_dibatalkan(): void
{
    $pesanan = \App\Models\Pesanan::create([
        'kode_pesanan' => 'PSN-KONFLIK01',
        'nama_pelanggan' => 'Tia Anggraeni',
        'sku_produk' => 'KRD-001',
        'jumlah' => 2,
        'status' => 'dibatalkan',
    ]);

    $response = $this->postJson(
        '/api/v1/pesanan/' . $pesanan->kode_pesanan . '/konfirmasi'
    );

    $response
        ->assertStatus(409)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath(
            'type',
            'https://layanan-pemesanan.test/problem/konflik-status'
        )
        ->assertJsonPath('status', 409);
}

public function test_dapat_membatalkan_pesanan_yang_masih_menunggu(): void
{
    $pesanan = \App\Models\Pesanan::create([
        'kode_pesanan' => 'PSN-BATAL01',
        'nama_pelanggan' => 'Tia Anggraeni',
        'sku_produk' => 'KRD-001',
        'jumlah' => 2,
        'status' => 'menunggu',
    ]);

    $response = $this->postJson(
        '/api/v1/pesanan/' . $pesanan->kode_pesanan . '/batal'
    );

    $response
        ->assertStatus(200)
        ->assertJsonPath('data.kode_pesanan', 'PSN-BATAL01')
        ->assertJsonPath('data.status', 'dibatalkan');

    $this->assertDatabaseHas('pesanans', [
        'kode_pesanan' => 'PSN-BATAL01',
        'status' => 'dibatalkan',
    ]);
}

    public function test_gagal_membatalkan_pesanan_yang_sudah_dikonfirmasi(): void
    {
    	$pesanan = \App\Models\Pesanan::create([
        	'kode_pesanan' => 'PSN-KONFLIK02',
        	'nama_pelanggan' => 'Tia Anggraeni',
        	'sku_produk' => 'KRD-001',
        	'jumlah' => 2,
        	'status' => 'dikonfirmasi',
    	]);

    	$response = $this->postJson(
        	'/api/v1/pesanan/' . $pesanan->kode_pesanan . '/batal'
    	);

    	$response
        	->assertStatus(409)
        	->assertHeader('Content-Type', 'application/problem+json')
        	->assertJsonPath(
            		'type',
            		'https://layanan-pemesanan.test/problem/konflik-status'
        	)
    		->assertJsonPath('status', 409);
    }
}
