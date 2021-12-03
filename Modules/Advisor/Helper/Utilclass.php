<?php

namespace Modules\Advisor\Helper;

use DB;


class Utilclass
{

    public function isAuthenticated($id, $token)
    {


        //Check if this is a valid token
        $myToken = DB::table('users')->where('id', $id)->value('token');

        if ($token != $myToken) {

            return false;
        } else {
            return true;
        }

    }

    public function sendPushNotificationToAdvisor($userID, $title, $body)
    {

        $FcmToken = DB::table('advisors')->where('id', $userID)->pluck('advisorFcmToken')->first();

        $notification = array("title" => $title, "body" => $body, "sound" => "default");
        $temp = array("to" => $FcmToken, "notification" => $body);
        $url = "https://fcm.googleapis.com/fcm/send";

        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => "key=AAAAOjo3zjI:APA91bFWFfAbN1ZqSTtRIFCNzMmy2QsUnhUtculD8IVvaBnPO5gJhTV7tSwAt-YQpf1RDmsOq7TfLx3gWtX1SfDyGIViTDxexVsGRqArmaM9kn0U83Q1kfptOFxRkXfeZka0RvpjC4l6"
            ]
        ]);

        if (empty($FcmToken)) {
            return;
        }


        $response = $client->post($url, ['body' => json_encode($temp)]);

        return $response;


    }

    public function sendPushNotificationToAdvisorAndroid($userID, $title, $body)
    {

        $FcmToken = DB::table('advisors')->where('id', $userID)->pluck('advisorFcmToken')->first();

        $notification = array("title" => $title, "body" => $body, "sound" => "default");
        $temp = array("to" => $FcmToken, "data" => $body);
        $url = "https://fcm.googleapis.com/fcm/send";

        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => "key=AAAAOjo3zjI:APA91bFWFfAbN1ZqSTtRIFCNzMmy2QsUnhUtculD8IVvaBnPO5gJhTV7tSwAt-YQpf1RDmsOq7TfLx3gWtX1SfDyGIViTDxexVsGRqArmaM9kn0U83Q1kfptOFxRkXfeZka0RvpjC4l6"
            ]
        ]);

        if (empty($FcmToken)) {
            return;
        }


        $response = $client->post($url, ['body' => json_encode($temp)]);

        return $response;


    }

    public function sendPushNotificationToUser($userID, $title, $body)
    {

        $FcmToken = DB::table('users')->where('id', $userID)->pluck('userFcmToken')->first();

        $notification = array("title" => $title, "body" => $body, "sound" => "default");
        $temp = array("to" => $FcmToken, "notification" => $body);
        $url = "https://fcm.googleapis.com/fcm/send";

        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json',
                    'Authorization' => "key=AAAAOjo3zjI:APA91bFWFfAbN1ZqSTtRIFCNzMmy2QsUnhUtculD8IVvaBnPO5gJhTV7tSwAt-YQpf1RDmsOq7TfLx3gWtX1SfDyGIViTDxexVsGRqArmaM9kn0U83Q1kfptOFxRkXfeZka0RvpjC4l6"
            ]
        ]);

        if (empty($FcmToken)) {
            return;
        }

        $response = $client->post($url, ['body' => json_encode($temp)]);


        return $response;


    }

    public function sendPushNotificationToUserAndroid($userID, $title, $body)
    {

        $FcmToken = DB::table('users')->where('id', $userID)->pluck('userFcmToken')->first();

        $notification = array("title" => $title, "body" => $body, "sound" => "default");
        $temp = array("to" => $FcmToken, "data" => $body);
        $url = "https://fcm.googleapis.com/fcm/send";

        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => "key=AAAAOjo3zjI:APA91bFWFfAbN1ZqSTtRIFCNzMmy2QsUnhUtculD8IVvaBnPO5gJhTV7tSwAt-YQpf1RDmsOq7TfLx3gWtX1SfDyGIViTDxexVsGRqArmaM9kn0U83Q1kfptOFxRkXfeZka0RvpjC4l6"
            ]
        ]);

        if (empty($FcmToken)) {
            return;
        }


        $response = $client->post($url, ['body' => json_encode($temp)]);


        return $response;


    }

    public function sendPushNotificationToGroup($GroupName, $title, $body)
    {

        $FcmToken = DB::table('groupfcmtokens')->where('GroupName', $GroupName)->pluck('GroupToken')->first();


        $notification = array("title" => $title, "body" => $body, "sound" => "default");
        $temp = array("to" => $FcmToken, "notification" => $body);
        $url = "https://fcm.googleapis.com/fcm/send";

        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => "key= AAAAhf3z1Ec:APA91bHPalWRjzPRATLMKGzNflfLNaFQ7dkfqJG4RAJNh8fO3zMtnH47opLH552gF_eVVO9m1x3lpauagN592L9GzGdlruj2UuIHTjo0Y42S2P3aencI9oWXkgpxt10n5MpDWcmLzXug"
            ]
        ]);

        $response = $client->post($url, ['body' => json_encode($temp)]);


        return $response;


    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

//   public function sendPushNotification($userID,$title,$body)
//   {

//     $FcmToken = DB::table('users')->where('id', $userID)->pluck('deviceId')->first();

//         $notification = array("title" => $title, "body" => $body, "sound" => "default");

//         $temp = array("to" => $FcmToken, "notification" => $notification);

//         $url = "https://fcm.googleapis.com/fcm/send";

//             $client = new \GuzzleHttp\Client([
//             'headers' => [ 'Content-Type' => 'application/json',
//             'Authorization' => "key=AAAABf22rsg:APA91bF6enOFcFiL28U4_oftVmEDhZ8JL0ep8Fx-Jj6F4lAXf9Pvrg6UjP12vfA1rrENYroO0X7cVfcssUTvLNKmKNuRlMXU9DEDIbtfRV7P7oGnseGf-0VIAk_QqcYR0dDtta8GdNS-"
//              ]
//         ]);

// if (empty($FcmToken)) 
// {
//  return;
// }

// $response = $client->post($url, [ 'body' => json_encode($temp) ]);


// return $response;

//   } 

}