<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveGame extends Model
{
    protected $table = "live_games";
    protected $fillable = ['id', 'a_id', 'b_id', 'a_score', 'b_score', 'narrator', 'image', 'start_time', 'status', 'created_at', 'updated_at'];

    public function aTeam()
    {
        return $this->hasOne('App\LiveTeam', 'a_id');
    }

    public function bTeam()
    {
        return $this->hasOne('App\LiveTeam', 'b_id');
    }
}
