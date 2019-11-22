<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveTeam extends Model
{
    protected $table = "live_teams";
    protected $fillable = ['id', 'name', 'image', 'type', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gamesAsA()
    {
        return $this->hasMany('App\LiveGame', 'a_id');
    }

    public function gamesAsB()
    {
        return $this->hasMany('App\LiveGame', 'b_id');
    }

    public function games()
    {
        return $this->gamesAsA->union($this->gamesAsB);
    }
}
