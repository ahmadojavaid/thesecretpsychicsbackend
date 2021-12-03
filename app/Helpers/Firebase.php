<?php
/**
 * Created by PhpStorm.
 * User: JBBravo
 * Date: 08-Jul-19
 * Time: 12:29 PM
 */

namespace App\Helpers;


use GuzzleHttp\Client;

class Firebase
{
    public function sendPushNotificationToAndroid($body, $fcmToken)
    {

        $url = "https://fcm.googleapis.com/fcm/send";
        $requestBody = array("to" => $fcmToken, "data" => $body);
        $authKey = "key=AAAAop-oreg:APA91bEduQU061VlJrVvz2QXbyUuLEXuL_ZB_Umb9pm9tKT0gw7eK27GDwm5YpfIq6hHHX1Jb9YF1VmJ68MBTB0FhZdWIvrE5B5l86wbVT9XcV3rRxuzAnJoFQ_8cDZXOLJ2vtDPl9mB";

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => $authKey
            ]
        ]);

        $response = $client->post($url, json_encode($requestBody));

        return $response;


    }
    public function agentSendPushNotificationToAndroid($body, $fcmToken){
        $url = "https://fcm.googleapis.com/fcm/send";
        $requestBody = ['json' => ['to' => $fcmToken, 'data' => $body]];
        $authKey = "key=AAAAh4PgTq4:APA91bG5tsSfxQmmkCBWkZHzA62xwYfPxCYM9nQj9awewk5UEwxEoixizdqMmtYgYIzkupeKN5m9HkA3Fbau0djgNJWpyhght0JvJI4Z-zBlDbkOciZ152xA7g_1Ej7gKgGHomxFPpJj";

        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authKey
            ]
        ]);
        $response = $client->post($url, $requestBody);
        return $response;
    }
    public function clientSendPushNotificationToAndroid($body, $fcmToken){
        $url = "https://fcm.googleapis.com/fcm/send";
        $requestBody = ['json' => ['to' => $fcmToken, 'data' => $body]];
        $authKey = "key=AAAAop-oreg:APA91bEduQU061VlJrVvz2QXbyUuLEXuL_ZB_Umb9pm9tKT0gw7eK27GDwm5YpfIq6hHHX1Jb9YF1VmJ68MBTB0FhZdWIvrE5B5l86wbVT9XcV3rRxuzAnJoFQ_8cDZXOLJ2vtDPl9mB";

        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authKey
            ]
        ]);
        $response = $client->post($url, $requestBody);
        return $response;
    }

    public function sendPushNotificationToiOS($body, $fcmToken)
    {

        $url = "https://fcm.googleapis.com/fcm/send";
        $requestBody = array("to" => $fcmToken, "notification" => $body);
        $authKey = "key=AAAAQ2vdGIU:APA91bF2kPXYMqNEHqimx6_1a8o4FqPYegZ2G4GpvJJwx2ywzGV0PyRmYVvbMDpY9oZXWlxpSlOSK3yzNw_zz6Vl5zlXaWMO76H7Lz8cD9EJpyYLUdtZ4UZbQ_36Szid6oHeWU51V60H";

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => $authKey
            ]
        ]);

        $response = $client->post($url, ['body' => json_encode($requestBody)]);

        return $response;


    }
}