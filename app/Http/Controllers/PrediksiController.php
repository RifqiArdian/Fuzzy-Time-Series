<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Perjalanan;
use DB;
use Carbon\Carbon;
class PrediksiController extends Controller
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
        if($filter=='Minggu'){
            $charts = Perjalanan::select('tanggal','jumlahPenumpang',DB::raw('sum(jumlahPenumpang) as jumlah'),DB::raw('YEAR(tanggal) year, MONTH(tanggal) month'))
            ->whereMonth('tanggal','!=','08')
            ->orderBy('tanggal')
            ->groupBy(DB::raw('WEEK(tanggal)'))
            ->get();
        
        }elseif($filter=='Bulan' or $filter==null){
            $charts = Perjalanan::select('tanggal','jumlahPenumpang',DB::raw('sum(jumlahPenumpang) as jumlah'),DB::raw('YEAR(tanggal) year, MONTH(tanggal) month'))
            ->groupBy('year','month')
            ->orderBy('tanggal')
            ->whereMonth('tanggal','!=','08')
            ->get();
        }
        return view('prediksi',compact('charts','data','filter'));
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
        // $rata = Perjalanan::avg('jumlahPenumpang');
        // $Dmax = Perjalanan::orderBy('jumlahPenumpang','DESC')->first();
        // $Dmin = Perjalanan::orderBy('jumlahPenumpang','ASC')->first();
        // $data = Perjalanan::all();
        // $R = $Dmax->jumlahPenumpang-$Dmin->jumlahPenumpang;
        // $K = 1+3.332*log(count($data));
        // $I = $R/$K;
        // $mi = ($Dmax->jumlahPenumpang+$Dmin->jumlahPenumpang)/2;


        // $startYear = Perjalanan::orderBy('tanggal','ASC')->firts;
        // $endYear = request('endYear');
        if($request->get('tanggal')<'2018-10-02' or $request->get('tanggal')>'2019-08-31'){
            return view('errorPrediksi');
        }else{
            $predictedData = Perjalanan::select('tanggal','jumlahPenumpang',DB::raw('sum(jumlahPenumpang) as jumlahPenumpang'))
                ->where('tanggal',$request->get('tanggal'))
                ->groupBy('tanggal')->first();
            $predictionYear = $request->get('tanggal');

            // $data = Data::all();
            $data = Perjalanan::select('tanggal','jumlahPenumpang',DB::raw('sum(jumlahPenumpang) as jumlahPenumpang'))
                ->where('tanggal','<',$request->get('tanggal'))
                ->orderBy('tanggal','ASC')
                ->groupBy('tanggal')
                ->get();

            $numData = $data->count();
            $min = $data->min('jumlahPenumpang');
            $max = $data->max('jumlahPenumpang');
            // calculate interval I
            // a. calculate difference between Dvt, Dvt-1 then compute the average
            $av = 0;
            $difference = array();
            for ($i=1; $i < $numData; $i++) { 
                $difference[$i] = $data[$i]->jumlahPenumpang - $data[$i-1]->jumlahPenumpang;            
                $av = $av + abs($difference[$i]);           
            }
            
            $av = $av / ($numData-1);       

            $B = $av / 2;

            // $I = $this->getBase($B);
            
            // $m = ceil(($max - $min) / $I);
            $m = 1+3.322*log($numData,10);

            $m=round($m);
            $I = ($max-$min)/($m);
            $I=round($I);
            $u = array();

            $startInterval = $min;

            for ($i = 0; $i < $m; $i++) {
                $endInterval = $startInterval + $I;
                array_push($u, array('start' => $startInterval, 'end' => $endInterval));
                $startInterval = $startInterval + $I;
            }

            // fuzzified debt, calculate the fuzzy category of the data
            for ($i=0; $i < $numData; $i++) { 
                $this->getFuzzySet($u, $data[$i]);
            }

            // make fuzzy logic relationship

            $flr = array();
            
            for ($i=0; $i < $numData - 1; $i++) { 
                $ai = $data[$i]->getUi();
                $aj = $data[$i+1]->getUi();
                if (!$this->checkDuplicateRelationship($flr, $ai, $aj)) {
                    array_push($flr, array($ai, $aj));              
                }
            }

            // make flr group
            $flrg = array();
            foreach ($flr as $key => $value) {
                if (empty($flrg[$value[0]])) {
                    $flrg[$value[0]] = array($value[1]);
                } else {
                    array_push($flrg[$value[0]], $value[1]);
                }
            }

            // dd($flrg);

            $sumerror = 0;
            $pr = array(0);
            $af = array(0);
            $errorPrediction = array(0);
            for ($i=1; $i < $numData; $i++) { 
                $pr[$i] = $this->calcPrediction($flrg, $u, $data[$i-1]);
                
                $af[$i] = $pr[$i] - $data[$i]->jumlahPenumpang;
                $errorPrediction[$i] = abs($af[$i])/$data[$i]->jumlahPenumpang;
                $sumerror = $sumerror + $errorPrediction[$i];
            }

            $AFER = $sumerror/($numData-1);

            // $g = new Graph;
            // $g->name = date("Y-m-d h:i:sa");
            // $XAP = $this->getXAPString($data, $pr);
            // $g->x = $XAP[0];
            // $g->actual = $XAP[1];
            // $g->prediction = $XAP[2];
            // $g->confirmed = 0;

            $predictionResult = $this->calcPrediction($flrg, $u, $data[$numData-1]);
            $actualValueOfPredictedData = $predictedData->jumlahPenumpang;

            //calc error prediction
            // $err = abs(($actualValueOfPredictedData - $predictionResult)/$actualValueOfPredictedData);
            // $perr = $err / $actualValueOfPredictedData;
            $err = abs($predictionResult - $actualValueOfPredictedData);
            $perr = $err / $actualValueOfPredictedData;

            // $XAP[0] = $XAP[0].$predictionYear;
            // $XAP[1] = $XAP[1].$actualValueOfPredictedData;
            // $XAP[2] = $XAP[2].$predictionResult;


            // $pieces = explode("|", $XAP[1]);
            // dd($pieces);
            // $g->save();
            
            return view('hasilPrediksi',compact('data', 'difference', 'av', 'B', 'u', 'flr', 'flrg', 'pr', 'af', 'errorPrediction', 'AFER', 'startYear', 'endYear', 'predictionYear', 'predictionResult', 'err', 'perr', 'XAP','actualValueOfPredictedData', 'min','endInterval','max','I','m','numData'));
        }
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
    }

    private function getBase($base) {
        $initBase = 10000;
        if ($base > 10000) {
            $initBase = 10000;
        } elseif ($base > 1000) {
            $initBase = 1000;
        } elseif ($base > 100) {
            $initBase = 100;
        } elseif ($base > 10) {
            $initBase = 10;
        } elseif ($base > 1) {
            $initBase = 1;
        } elseif ($base > 0) {
            $initBase = 0.1;
        }

        return ceil($base*10 / $initBase) / 10 * $initBase;
    }

    private function getFuzzySet($u, $data) {
        
        foreach ($u as $key => $uItem) {
            if ($data->jumlahPenumpang >= $uItem['start'] && $data->jumlahPenumpang < $uItem['end']) {
                $data->setUi($key);
            }
        }
    }

    private function checkDuplicateRelationship($flr, $ai, $aj) {
        // echo "<hr>";
        // echo $ai, $aj;

        if (count($flr) == 0) {
            return false;
        }

        foreach ($flr as $key => $value) {   

            if ($ai == $value[0] && $aj == $value[1]) {
                return true;
            }

        }

        return false; 
    }

    private function calcPrediction($flrg, $u, $data) {
        if (empty($flrg[$data->getUi()])) {            
            return ($u[$data->getUi()]['start'] + $u[$data->getUi()]['end']) / 2;
        }

        $aj = $flrg[$data->getUi()];

        $sumOfMidPoint = 0;
        foreach ($aj as $key => $value) {
            
            $midPoint = ($u[$value]['start'] + $u[$value]['end']) / 2;
            
            $sumOfMidPoint = $sumOfMidPoint + $midPoint;
            
        }
        $result = $sumOfMidPoint / count($aj);
        return $result;
    }

    private function getXAPString($data, $pr) {
        $x = "";
        $actual = "";
        $prediction = "";
        foreach ($data as $key => $d) {
            $x = $x . $d->name . "|";
            $actual = $actual . $d->jumlahPenumpang . "|";
            if ($key == 0) {
                $prediction = $prediction . $d->jumlahPenumpang . "|";
            } else {
                $prediction = $prediction . $pr[$key] . "|";
            }           
        }

        //$x = substr($x, 0, strlen($x)-1);
        //$actual = substr($actual, 0, strlen($actual)-1);
        //$prediction = substr($prediction, 0, strlen($prediction)-1);

        return array($x, $actual, $prediction);
    }
}
