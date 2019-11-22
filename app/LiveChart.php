<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveChart extends Model
{
    protected $table = "live_charts";

    protected $fillable = ['id', 'game_id', 'user_id', 'content', 'status', 'created_at', 'updated_at'];

}
