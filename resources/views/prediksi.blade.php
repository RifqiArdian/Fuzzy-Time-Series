@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Prediksi</div>
                <div class="card-body">
                    <form class="form-inline justify-content-center" action="{{ route('prediksi.store') }}" method="POST" >
                        {{ csrf_field() }}
                        {{ method_field('POST') }}
                        <div class="row justify-content-center">
                            <div class="input-group mb-3 mr-3">
                                <input type="date" name="tanggal" placeholder="Tanggal" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-success btn-primary mb-3" onclick="return confirm('Yakin ingin membuat prediksi?')">Buat Prediksi</button>
                        </div>
                    </form>
                    <form class="form-inline" method="GET" action="{{ url('prediksi') }}">
                        <div class="input-group mb-3 mr-3">
                            <select name="filter" class="form-control">
                                <option value="Minggu">Minggu</option>
                                <option value="Bulan">Bulan</option>
                            </select>
                        </div>
                        <button class="btn btn-primary mb-3" style="width: 70px;">Filter</button>
                    </form>
                    <canvas id="myChart" width="400" height="400"></canvas>
                    <script>
                    $(function () {
                        var ctx = document.getElementById("myChart").getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                @if($filter=='Minggu')                                      
                                    labels: [
                                    "Okt 1","Okt 1","Okt 2","Okt 3","Okt 4", "Nov 1","Nov 2","Nov 3","Nov 4", "Des 1","Des 2","Des 3","Des 4","Des 5", "Jan 1","Jan 2","Jan 3","Jan 4","Jan 5", "Feb 1","Feb 2","Feb 3","Feb 4", "Mar 1","Mar 2","Mar 3","Mar 4","Mar 5", "Apr 1","Apr 2","Apr 3","Apr 4", "Mei 1","Mei 2","Mei 3","Mei 4", "Jun 1","Jun 2","Jun 3","Jun 4","Jun 5", "Jul 1","Jul 2","Jul 3","Jul 4"
                                    ],
                                @else
                                    labels: ["Okt 2018", "Nov 2018", "Des 2018", "Jan 2019", "Feb 2019", "Mar 2019", "Apr 2019", "Mei 2019", "Jun 2019", "Jul 2019"],
                                @endif
                                datasets: [{
                                    label: '# jumlah penumpang',
                                    data: [
                                        @foreach ($charts as $chart )
                                            {{$chart->jumlah}},
                                        @endforeach
                                    ],
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(153, 102, 255, 0.2)',
                                        'rgba(255, 159, 64, 0.2)'
                                    ],
                                    borderColor: [
                                        'rgba(255,99,132,1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero:true
                                        }
                                    }]
                                }
                            }
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
