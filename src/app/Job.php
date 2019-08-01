<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Job extends Model
{
    /**
     * 
     * 
     * @var array
     */
    protected $fillable = [
        'submitter_id', 'processor_id', 'command', 'completed','priority'
    ];
    /**
     * The storage format of the model's date columns.
     * 
     * NOTE: still need to determine the impact of this across multiple database engines
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * The attributes that should be mutated to dates.
     * 
     * By Default Eloquent comes with: 
     * created_at
     * updated_at
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

    /**
     * This is a custom fill function to ensure that everything is typed
     * and ready to deal with those not statically typed apps
     *
     * @var Request
     */
    public function typedFill(Request $request)
    {
        if( $request->input('submitter_id',false) ){
            $this->submitter_id = intval($request->input('submitter_id'));
        }
        if( $request->input('processor_id',false) ){
            $this->processor_id = intval($request->input('processor_id'));
        }
        if( $request->input('command',false) ){
            $this->command = $request->input('command');
        }
        if( $request->input('priority',false) ){
            $this->priority = intval($request->input('priority'));
        }
    }
}
