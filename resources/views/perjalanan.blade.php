@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Data Perjalanan</div>

                <div class="card-body">
                    <form class="form-inline" method="GET" action="{{ url('perjalanan') }}">
                        <div class="input-group mb-3 mr-3">
                            <select name="filter" class="form-control">
                                <option value="Hari">Hari</option>
                                <option value="Minggu">Minggu</option>
                                <option value="Bulan">Bulan</option>
                            </select>
                        </div>
                        <button class="btn btn-primary mb-3" style="width: 70px;">Filter</button>
                    </form>
                    <button type="button" name="btn-order" class="btn btn-success mb-3" data-toggle="modal" data-target="#form-tambah" style="float: right;">Tambah Data</button>
                    <div class="modal fade" id="form-tambah" tabindex="-1" role="dialog" aria-labelledby="form-orderLabel">
                        <div class="modal-dialog modal-dialog-centered" role="document">

                            <div class="modal-content">
                              <div class="modal-header card-header">
                                    <h4 style="color: black;">Tambah Data</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><i class="fa fa-times" style="color: white;"></i></button>
                                </div>
                                <form action="{{ route('perjalanan.store') }}" method="POST" >
                                  {{ csrf_field() }}
                                  {{ method_field('POST') }}
                                  <div class="modal-body">
                                    <div class="form-group order">   
                                          <div class="input-group mb-3">
                                             <input type="date" name="tanggal" placeholder="Tanggal" class="form-control">
                                         </div>
                                         <div class="input-group mb-3">
                                             <input type="time" name="jam" placeholder="Jam" class="form-control">
                                         </div>
                                         <div class="input-group mb-3">
                                             <input type="number" name="jumlahPenumpang" placeholder="Jumlah Penumpang" class="form-control">
                                         </div>
                                  </div>
                                </div>
                                  <div class="modal-footer">
                                      <button type="submit" class="btn btn-success" onclick="return confirm('Yakin ingin menambah data?')">Tambah</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                        </div>
                    <table class=" table table-sm table-bordered table-striped table-hover">
                        <thead class="table-secondary">
                            <tr>
                                <th style=" text-align: center;">No</th>
                                <th style=" text-align: center;">Tanggal</th>
                                <th style=" text-align: center;">Hari</th>
                                <th style=" text-align: center;">Minggu</th>
                                <th style=" text-align: center;">Bulan</th>
                                <th style=" text-align: center;">Libur</th>
                                <th style=" text-align: center;">Jumlah Penumpang</th>
                            </tr>
                        </thead>
                        @php
                            $no = 1;
                        @endphp
                        @if($filter=='Hari' or $filter==null)
                            @php
                                $no = ($perjalanans->currentpage()-1)* $perjalanans->perpage() + 1;
                            @endphp
                        @endif
                        <tbody>
                            @foreach($perjalanans as $perjalanan )
                            <tr>    
                                <td style="text-align: center;">{{ $no++ }}</td>
                                <td style="text-align: center;">
                                    @if($filter=='Hari' or $filter==null)
                                        {{ $perjalanan->tanggal->format('d-m-Y') }}
                                    @else
                                        {{ $perjalanan->minTanggal }} - {{ $perjalanan->maxTanggal }}
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($filter=='Hari' or $filter==null)
                                        {{ $perjalanan->tanggal->format('l') }}
                                    @else
                                        @php
                                            $mins = strtotime($perjalanan->minTanggal);
                                            $maxs = strtotime($perjalanan->maxTanggal);
                                            $min = date('l',$mins);
                                            $max = date('l',$maxs);
                                        @endphp
                                        {{ $min }} - {{  $max }}
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($filter=='Hari' or $filter=='Minggu' or $filter==null)
                                        {{ $perjalanan->tanggal->weekOfMonth }}
                                    @else
                                        @php
                                            $mins = strtotime($perjalanan->minTanggal);
                                            $maxs = strtotime($perjalanan->maxTanggal);
                                            $min = date('W',$mins);
                                            $max = date('W',$maxs);
                                        @endphp
                                        {{ $min }} - {{  $max }}
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    {{ $perjalanan->tanggal->format('F-Y') }}
                                </td>
                                <td style="text-align: center;">
                                @php $libur=\App\Libur::where('tanggal', $perjalanan->tanggal)->first() @endphp    
                                @if(!is_null($libur))
                                {{\App\Libur::where('tanggal', $perjalanan->tanggal)->pluck('nama')->first()}}
                                @else
                                -
                                @endif    
                                </td>
                                <td style="text-align: center;">{{ $perjalanan->jumlahPenumpang }}</td>
                            </tr>
                            @endforeach    
                        </tbody>
                    </table>
                    @if($filter=='Hari' or $filter==null)
                        {{$perjalanans->links()}}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
