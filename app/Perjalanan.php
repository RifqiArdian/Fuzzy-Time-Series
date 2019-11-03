<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Perjalanan extends Model
{
	protected $fillable = ['tanggal', 'jam', 'jumlahPenumpang'];

	protected $dates = ['tanggal'];

	protected $ui = -1;

    public function setUi($u) {
    	$this->ui = $u;
    }

    public function getUi() {
    	return $this->ui;
    }
}
