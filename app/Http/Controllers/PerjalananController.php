<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Perjalanan;
use DB;
use Charts;
use Carbon\Carbon;

class PerjalananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $filter=$request->get('filter');
        if($filter=='Bulan'){
            $perjalanans = Perjalanan::select('tanggal',DB::raw('sum(jumlahPenumpang) as jumlahPenumpang'),DB::raw("DATE_FORMAT(tanggal, '%m-%Y') new_date"),DB::raw('YEAR(tanggal) year, MONTH(tanggal) month'),DB::raw('max(tanggal) as maxTanggal'),DB::raw('min(tanggal) as minTanggal'))
            ->orderBy('tanggal','ASC')
            ->groupBy('month','year')
            ->whereMonth('tanggal','!=','08')
            ->get();
        }elseif($filter=='Minggu'){
            $perjalanans = Perjalanan::select('tanggal',DB::raw('sum(jumlahPenumpang) as jumlahPenumpang'),DB::raw('max(tanggal) as maxTanggal'),DB::raw('min(tanggal) as minTanggal'))
            ->orderBy('tanggal','ASC')
            ->groupBy(DB::raw('WEEK(tanggal)'))
            ->whereMonth('tanggal','!=','08')
            ->get();
            // ->groupBy(function($date) {
            //     return Carbon::parse($date->tanggal)->format('W');
            // });
        }elseif($filter=='Hari' or $filter==null){
            $perjalanans = Perjalanan::select('tanggal',DB::raw('sum(jumlahPenumpang) as jumlahPenumpang'))
            ->orderBy('tanggal','ASC')
            ->groupBy('tanggal')
            ->whereMonth('tanggal','!=','08')
            ->paginate(10);
        }
        return view('perjalanan', compact('perjalanans','filter'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        Perjalanan::create(
            request(
                ['tanggal', 'jam', 'jumlahPenumpang']
            )
        );
        return redirect('/perjalanan')->with('status', 'Data telah ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $data = Perjalanan::where('id',$id)->first();
        $data->delete();     

        return redirect('/perjalanan')->with('status', 'Data telah dihapus.');
    }
}
