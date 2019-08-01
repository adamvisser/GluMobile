<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Job;

// Since we need to leverage the use of locks we will create a full class instead of leveraging the built in resource types
class JobController extends Controller
{
    /**
     * Retrieve the job for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($jobID = 0)
    {
        // Lumen makes sure that when this throws an exception then our API with throw a 404
        $job = $job = Job::findOrFail($jobID);
        return response()->json($job);
    }

    /**
     * Update the job for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $jobID)
    {
        // Since we need to consider the database locks from "grab" we will want a lock here to prevent the issue of selecting something for processing that is being updated.
        app('db')->beginTransaction();
        // First we want to get the item and lock it up for an update
        try {
            $job = Job::where('id',$jobID)->lockForUpdate()->first();
            if(!$job){
                throw new \Exception('Item Not Found');
            }
        } catch (\Exception $e) {
            // cant find an item and we just tried to start a transaction and a locking
            app('db')->rollback();
            abort(404);
        }
        try {
            //with a locked job in hand lets fill it on up...
            // I opted to not do the following...
            // $job->fill($request->all());
            // so that we would be able to accomodate for strongly typed languages that might interact with return values, rather than re-requesting values (which is not cool)
            $job->typedFill($request);
            $job->save();
            app('db')->commit();
        } catch (\Exception $e) {
            // Here we have an actual error to worry about
            app('db')->rollback();
            return response()->json([
                'error_message'=>$e,
                'error'=>true
            ]);
        }
        return response()->json($job);
    }

    /**
     * Create a new Job with the given POST information
     *
     * Example Good Response
     * 
        {
            "submitter_id": "1",
            "command": "docker ps",
            "priority": 0,
            "completed": false,
            "updated_at": "2019-07-25 00:32:44",
            "created_at": "2019-07-25 00:32:44",
            "id": 2
        }
     * 
     * @param  int  $id
     * @return Response
     */
    public function put(Request $request)
    {
        // This is where we input a new job. Fail First Methodology of course
        // We can set a default priority and we set the "inProgress" to false of course. A point of contention on this implementation would be whether or not to allow a user to input a job that is already in progress. 
        $submitterID = $request->input('submitter_id',false);
        $command = $request->input('command',false);
        // All three of the above will be required for a job to be worked with
        if(!$submitterID && !$command){
            return response()->json([
                'error_message'=>'Command, and Submitter ID are required to submit a new job.',
                'error'=>true
            ]);
        }

        $job = new Job;
        $job->submitter_id = intval($submitterID);
        $job->command = $command;
        // If a priority level is not submitted well just put it at 0
        $job->priority = intval($request->input('priority',0));
        // We would have to change this if we want to allow for the submission of created jobs
        $job->completed = false;
        // We know that we have what we need in hand for a Job so lets submit that
        try {
            $job->save();
        } catch (\Exception $e) {
            return response()->json([
                'error_message'=>$e,
                'error'=>true
            ]);
        }
        return response()->json($job);
    }

    /**
     * Retrieve a job for processing. 
     * 
     * Example Good Return Object:
     * 
        {
            "id": 1,
            "submitter_id": 1,
            "processor_id": 1,
            "command": "docker ps",
            "priority": 0,
            "completed": 0,
            "created_at": "2019-07-24 21:37:04",
            "updated_at": "2019-07-24 23:59:37"
        }
     *
     * @param  int  $id
     * @return Response
     */
    public function grab()
    {
        // Transactions being used to signify that this is a single atomic set of functions
        app('db')->beginTransaction();
        try {
            
            // Lumen allows for locking of database entries for update. This will handle the "no two should have the same" requirement. This would break once we stop using data storages that allow for locking.
            $job = Job::where('processor_id',0)->where('completed',false)->orderBy('priority', 'desc')->lockForUpdate()->first();
            //will require this to be input in a moment
            $job->processor_id = 1;
            $job->save();
            // With the above set, finish it off
            app('db')->commit();
        } catch (\Exception $e) {
            // Something is wrong... 
            app('db')->rollback();
            return response()->json([
                'error_message'=>$e,
                'error'=>true
            ]);
        }
        return response()->json($job);
    }

    /**
     * Retrieve all the jobs
     * 
     * Example Good Return List
     * 
        [
            {
                "id": 1,
                "submitter_id": 1,
                "processor_id": 0,
                "command": "docker ps",
                "priority": 0,
                "completed": 0,
                "created_at": "2019-07-24 21:37:04",
                "updated_at": "2019-07-24 21:37:04"
            }
        ]
     *
     * @return Response
     */
    public function list()
    {
        return response()->json(Job::all());
    }
}