<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Kyc extends Model
{
    protected $fillable = [
        'user_id',
        'passport_country',
        'arrival_date',
        'departure_date',
        'reason',
        'passport_hash',
        'passport_id_encrypted',
        'nationality',
        'gender'
    ];
    
    protected $visible = ['arrival_date','departure_date','reason','passport_country','nationality','gender','verified_at','created_at','updated_at'];
  protected $casts = [
    'arrival_date'   => 'date',
    'departure_date' => 'date',
    'verified_at'    => 'datetime',
  ];
  
  protected $appends = ['passport_id'];
  protected $hidden  = ['passport_id_encrypted'];

  public function user(){ return $this->belongsTo(User::class); }

  public function setPassportIdAttribute($value){
    $this->attributes['passport_id_encrypted'] = Crypt::encryptString($value);
  }
  public function getPassportIdAttribute(){
    return isset($this->attributes['passport_id_encrypted'])
      ? Crypt::decryptString($this->attributes['passport_id_encrypted'])
      : null;
  }
}
