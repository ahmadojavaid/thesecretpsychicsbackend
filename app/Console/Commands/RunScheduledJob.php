<?php

namespace App\Console\Commands;

use App\Agent;
use App\Helpers\Firebase;
use App\Http\Models\API\AgentBadges;
use App\Http\Models\API\Job;
use App\User;
use Illuminate\Console\Command;

class RunScheduledJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:RunScheduleJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $maxDistance = 1000000000000;
        $currentDateTime = date('Y-m-d H:i:s');
        $scheduledJob = Job::where('is_scheduled',1)
            //->where('job_start_datetime',$currentDateTime)
            ->where('job_status',5)
            ->orderbyDesc('job_start_datetime')
            ->first();

        if(!empty($scheduledJob)){
            $agentCollection = AgentBadges::where('badge_id', '=', $scheduledJob->security_type_id)
                ->pluck('agent_id');

            $findAgent = Agent::whereIn('id', $agentCollection)
                ->where('status', '=', 1)
                ->where('attire_type', '=', $scheduledJob->is_uniformed)
                ->where('have_vehicle', '=', $scheduledJob->can_drive)
                ->selectRaw('id,fcm_token,status,attire_type,have_vehicle, (3959 * acos( cos( radians(' . $scheduledJob->job_lat .
                    ') ) * cos( radians( current_lat ) ) * cos( radians( current_long ) - radians(' .
                    $scheduledJob->job_long . ') ) + sin( radians(' . $scheduledJob->job_lat . ') ) * sin( radians( current_lat ) ) ) ) AS distance')
                ->having('distance', '<', $maxDistance)
                ->orderBy('distance')
                ->get();

            if (count($findAgent) == 0) {
                return response()->json([
                    'statusCode' => '0',
                    'statusMessage' => 'No agents available nearby! please try again',
                    'Result' => null
                ]);
            }

            // dd($findAgent);
            //updating user status

            $user = User::find($scheduledJob->user_id)->update([
                "status" => 2
            ]);

            $arr = [];
            if (count($findAgent) > 0) {

                for ($i = 0; $i < count($findAgent); $i++) {

                    $notificationBody = array(
                        'body' => 'New Job Alert!',
                        'status' => 1,
                        'jobId' => $scheduledJob->id,
                        'client_id' => $scheduledJob->user_id,
                        'client_longitude' => $scheduledJob->job_lat,
                        'client__latitude' => $scheduledJob->job_long
                    );

                    $helperFunc = new Firebase();
                    $agentFCMToken = $findAgent[$i]->fcm_token;
                    // dd($findAgent);
                    if ($findAgent[$i]->device_type == 2) {
                        $helperFunc->agentSendPushNotificationToAndroid($notificationBody, $agentFCMToken);
                    } else {
                        $helperFunc->sendPushNotificationToiOS($notificationBody, $agentFCMToken);
                    }
                    $arr[] = $findAgent[$i]->id;
                    // updating agent status to 6; which will be used for showing timer screen on agent screen
                    Agent::where('id', '=', $findAgent[$i]->id)->update([
                        "status" => 6
                        // "status" => 6
                    ]);
                }


                $userNotificationBody = array(
                    'body' => 'Find guard for your scheduled job',
                    'status' => 1,
                    'jobId' => $scheduledJob->id,
                    'client_id' => $scheduledJob->user_id,
                    'client_longitude' => $scheduledJob->job_lat,
                    'client__latitude' => $scheduledJob->job_long
                );

                $helperFunc->clientSendPushNotificationToAndroid($userNotificationBody, $user->fcm_token);


                $job = Job::where('id', '=', $scheduledJob->id)->first();
                $job->notification_agent = $arr;
                $job->job_status = 1;
                $job->save();
                // if(count($findAgent) > 0){

                $jobDetails = Job::where('id', '=', $scheduledJob->id)->first();
                return response()->json([
                    'statusCode' => '1',
                    'statusMessage' => 'Job Created and Notification Sent',
                    'Result' => [
                        'jobDetails' => $jobDetails,
                        'agentDetails' => null,
                    ]
                ]);
            } else {
                return response()->json([
                    'statusCode' => '0',
                    'statusMessage' => 'No agents available nearby! please try again',
                    'Result' => null
                ]);
            }
        }
    }
}
