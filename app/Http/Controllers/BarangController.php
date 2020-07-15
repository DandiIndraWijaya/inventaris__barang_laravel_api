<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Riwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $explode = explode(',', $request->gambar);
        $decode = base64_decode($explode[1]);

        if(str_contains($explode[0], 'jpeg')){
            $extentsion = 'jpg';
        }else{
            $extentsion = 'png';
        }

        $str_random = Str::random(10);
        $fileName = $str_random.'.'.$extentsion;

        $path = public_path().'/'.$fileName;

        file_put_contents($path, $decode);
        // $riwayat = [
        //     'kode_barang' => $request->kode,
        //     'peminjam' => null,
        //     'alamat_peminjam' => null,
        //     'kontak' => null,
        //     'tenggat' => null,
        //     'aktif' => 0
        // ];
        // Riwayat::create($riwayat);

        $data = [
            'nama_barang' => $request->nama_barang,
            'kode' => $request->kode,
            'deskripsi' => $request->deskripsi,
            'gambar' => $fileName
        ];
        $barang = Barang::create($data);

        return response($barang, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Barang  $barang
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {   

        $nama_barang = $request->cari;
        $cari = Barang::select('kode')->where('barang.nama_barang', 'like', '%'.$nama_barang.'%')->get();
        $kolom = [];

        for($i = 0 ; $i < count($cari) ; $i++){
            $kolom[$i] = $cari[$i]->kode;
        }

        $barang = [];

        $barang1 = Barang::select('barang.id', 'barang.gambar','barang.kode', 'barang.nama_barang', 'barang.deskripsi', 'riwayat.peminjam', 'riwayat.tenggat', 'riwayat.created_at', 'riwayat.alamat_peminjam', 'riwayat.kontak', 'riwayat.keperluan')->leftJoin('riwayat', 'barang.kode', '=', 'riwayat.kode_barang')->whereIn('barang.kode', $kolom)->where('riwayat.aktif', '!=', 2)
                    ->get();
        
        if($barang1 != null){
            for( $i = 0 ; $i < count($barang1) ;  $i++){
                $tenggat1 = strtotime($barang1[$i]->tenggat);
                $created_at1 = strtotime($barang1[$i]->created_at);

                if($tenggat1 < time() - $created_at1){
                    $barang1[$i]->ket = "Sudah Melebihi Tenggat";
                }else{
                    $barang1[$i]->ket = "-";
                }

                $explode = explode('T', $barang1[$i]->tenggat);
                $explode_tanggal = explode('-', $explode[0]);
                $tenggat = $explode[1] . ', ' . $explode_tanggal[2] . '-' . $explode_tanggal[1] . '-' . $explode_tanggal[0];
                $barang1[$i]->tenggat = $tenggat;

                array_push($barang, $barang1[$i]);
            }
        }

        $barang2 = Barang::select('barang.id', 'barang.gambar','barang.kode', 'barang.nama_barang', 'barang.deskripsi')->whereIn('barang.kode', $kolom)->where('barang.aktif', '=', 0)
                    ->get();

        if($barang2 != null){
            for( $i = 0 ; $i < count($barang2) ;  $i++){
                array_push($barang, $barang2[$i]);
            }
        }     
        return response($barang, 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Barang  $barang
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $kode = $request->kode;
        $peminjam = $request->peminjam;
        $alamat = $request->alamat_peminjam;
        $kontak = $request->kontak;
        $tenggat = $request->tenggat;
        $keperluan = $request->keperluan;

        $riwayat = [
            'kode_barang' => $kode,
            'peminjam' => $peminjam,
            'keperluan' => $request->keperluan,
            'alamat_peminjam' => $alamat,
            'kontak' => $kontak,
            'tenggat' => $tenggat,
            'aktif' => 1
        ];

        $cek = Barang::where('kode', $kode)->get();

        if($cek[0]->aktif == 0){
            $cek2 = Riwayat::create($riwayat);
            if($cek2){
                Barang::where('kode', $kode)->update(['aktif' => 1]);
                return response('Data Berhasil Disimpan', 201);
            }
           
        }else{
            return response('Barang sudah dipinjam', 201);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Barang  $barang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $kode = $request->kode;
        $tenggat = $request->tenggat;

        Barang::where('kode', $kode)->update(['aktif' => 0]);
        Riwayat::where('kode_barang', $kode)->where('tenggat', $tenggat)->update(['aktif' => 2]);

        return response('Berhasil Diupdate', 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Barang  $barang
     * @return \Illuminate\Http\Response
     */
    public function destroy(Barang $barang)
    {
        //
    }

    public function history(Request $request){
        $cari = $request->data;
        $riwayat = $request->i;

        if($riwayat == 'tanggal'){
            $hasil = Riwayat::select('barang.nama_barang', 'riwayat.id', 'riwayat.kode_barang', 'riwayat.peminjam', 'riwayat.alamat_peminjam', 'riwayat.kontak', 'riwayat.aktif', 'riwayat.keperluan', 'riwayat.tenggat', 'riwayat.created_at', 'riwayat.updated_at')->join('barang', 'barang.kode', '=', 'riwayat.kode_barang')->where('riwayat.created_at', 'like', '%'. $cari . '%')->get();
            
            for($i = 0; $i < count($hasil) ; $i++){
                $explode = explode(' ', date($hasil[$i]->created_at));
                $explode_tanggal = explode('-', $explode[0]);
                $jam = explode(':', $explode[1]);
                $created_at = $jam[0] . ':' . $jam[1] . ', ' . $explode_tanggal[2] . '-' . $explode_tanggal[1] . '-' . $explode_tanggal[0];
                $hasil[$i]->dipinjam = $created_at;

                $explode = explode(' ', date($hasil[$i]->updated_at));
                $explode_tanggal = explode('-', $explode[0]);
                $jam = explode(':', $explode[1]);
                $updated_at = $jam[0] . ':' . $jam[1] . ', ' . $explode_tanggal[2] . '-' . $explode_tanggal[1] . '-' . $explode_tanggal[0];
                $hasil[$i]->dikembalikan = $updated_at;

                if(strtotime($hasil[$i]->tenggat) < time() - strtotime($hasil[$i]->created_at) AND $hasil[$i]->aktif == 1){
                    $hasil[$i]->status = "Melebihi tenggat";
                }else if(strtotime($hasil[$i]->tenggat) > time() - strtotime($hasil[$i]->created_at) AND $hasil[$i]->aktif == 1){
                    $hasil[$i]->status = "Masih dipinjam";
                }
            }

            return response($hasil, 201);
        }else if($riwayat == 'kode'){
            $hasil = Riwayat::select('barang.nama_barang', 'riwayat.id', 'riwayat.kode_barang', 'riwayat.peminjam', 'riwayat.alamat_peminjam', 'riwayat.kontak', 'riwayat.keperluan', 'riwayat.tenggat', 'riwayat.created_at', 'riwayat.updated_at', 'riwayat.aktif')->join('barang', 'barang.kode', '=', 'riwayat.kode_barang')->where('kode_barang', 'like', '%'. $cari . '%')->get();
            
            for($i = 0; $i < count($hasil) ; $i++){
                $explode = explode(' ', date($hasil[$i]->created_at));
                $explode_tanggal = explode('-', $explode[0]);
                $jam = explode(':', $explode[1]);
                $created_at = $jam[0] . ':' . $jam[1] . ', ' . $explode_tanggal[2] . '-' . $explode_tanggal[1] . '-' . $explode_tanggal[0];
                $hasil[$i]->dipinjam = $created_at;

                $explode = explode(' ', date($hasil[$i]->updated_at));
                $explode_tanggal = explode('-', $explode[0]);
                $jam = explode(':', $explode[1]);
                $updated_at = $jam[0] . ':' . $jam[1] . ', ' . $explode_tanggal[2] . '-' . $explode_tanggal[1] . '-' . $explode_tanggal[0];
                $hasil[$i]->dikembalikan = $updated_at;

                if(strtotime($hasil[$i]->tenggat) < time() AND $hasil[$i]->aktif == 1){
                    $hasil[$i]->status = "Melebihi tenggat";
                    $hasil[$i]->dikembalikan = null;
                }elseif($hasil[$i]->aktif == 0){
                    $hasil[$i]->dikembalikan = $updated_at;
                }else{
                    $hasil[$i]->status = "Masih dipinjam";
                    $hasil[$i]->dikembalikan = null;
                }
                
            }
            return response($hasil, 201);
            
        }
    }
}
