<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class kelasController extends Controller
{

    // =====================
    // GET ALL KELAS
    // =====================
    public function index()
    {
        $kelas = Kelas::all();

        return response()->json([
            'status' => true,
            'message' => 'Data kelas berhasil diambil',
            'data' => $kelas
        ]);
    }

    // =====================
    // CREATE KELAS
    // =====================
    public function store(Request $request)
    {

        $request->validate([
            'nama_kelas' => 'required'
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas
        ],201);
    }

    // =====================
    // GET DETAIL KELAS
    // =====================
    public function show($id)
    {

        $kelas = Kelas::find($id);

        if(!$kelas){
            return response()->json([
                'status' => false,
                'message' => 'Kelas tidak ditemukan'
            ],404);
        }

        return response()->json([
            'status' => true,
            'data' => $kelas
        ]);
    }

    // =====================
    // UPDATE KELAS
    // =====================
    public function update(Request $request, $id)
    {

        $kelas = Kelas::find($id);

        if(!$kelas){
            return response()->json([
                'status' => false,
                'message' => 'Kelas tidak ditemukan'
            ],404);
        }

        $kelas->update([
            'nama_kelas' => $request->nama_kelas ?? $kelas->nama_kelas
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kelas berhasil diupdate',
            'data' => $kelas
        ]);
    }

    // =====================
    // DELETE KELAS
    // =====================
    public function destroy($id)
    {

        $kelas = Kelas::find($id);

        if(!$kelas){
            return response()->json([
                'status' => false,
                'message' => 'Kelas tidak ditemukan'
            ],404);
        }

        $kelas->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kelas berhasil dihapus'
        ]);
    }

}