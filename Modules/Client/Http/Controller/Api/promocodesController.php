<?php

namespace Modules\Client\Http\Controller\Api;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\CustomData\Utilclass;
use Illuminate\Support\Facades\Hash;
use App\PromoCodes;
use Illuminate\Support\Facades\Mail;
use DB;
use \Carbon;

class promocodesController extends ApiController
{  

    //   public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

public function store(Request $request)
    { 
        // $PromoCodes = PromoCodes::create($request->all()); 
             $Activities = new PromoCodes();

        if (PromoCodes::where('promo_code','=',Input::get('promo_code'))->exists()) 
         {
         return response()->json(['statusCode'=>'400','statusMessage'=>'promo code Already Exists','Result'=>NULL]);              
         }
   
          $Activities = PromoCodes::create($request->all());
         
       return response()->json(['statusCode'=>'1','statusMessage'=>'Promo Code Created','Result'=>$Activities]); 
      } 

  public function update($id,Request $request)
  {     
       $Category=PromoCodes::find($id);

           if(!$Category)
          {
           return response()->json(['statusCode'=>'0','statusMessage'=>'Record Not Found','Result'=>NULL]);
          }  
             $Category->update($request->all());
  
     return response()->json(['statusCode'=>'1','statusMessage'=>'Promo Code Data is Updated','Result'=>$Category]);

  }  
      public function show($id)
       {     
           $Messages=PromoCodes::find($id);
    
        return response()->json(['statusCode'=>'1','statusMessage'=>'showing user Promo Code','Result'=>$Messages]);
      }

      public function index()
       {     
           $Messages=PromoCodes::all();
  
        return response()->json(['statusCode'=>'1','statusMessage'=>'showing all Promo Codes','Result'=>$Messages]);
      } 
      public function destroy($id,Request $request)
    {   
           $Messages=PromoCodes::find($id);


       if(!$Messages)
      {
       return response()->json(['statusCode'=>'0','statusMessage'=>'Record Not Found','Result'=>NULL]); 
      }   
           $Messages->delete();

       return response()->json(['statusCode'=>'1','statusMessage'=>'Promo Code Successfully deleted','Result'=>NULL]); 
      } 
      
          public function verifyPromo(Request $request)
    {   
        $userId = $request->input('userId');
        $promo_code = $request->input('promo_code');
          
         $mytime = Carbon\Carbon::now();
        // return $mytime->toDateTimeString();

            if ($request->has('promo_code')) { 

                 $checkTypeOfPRomoCode = DB::table('promo_codes')  
                                ->where('promo_code', '=', $promo_code)
                                ->first();
               
                if (!$checkTypeOfPRomoCode) {
                             return response()->json(['statusCode' => '0', 'statusMessage' => 'Invlaid promo code', 'Result' => NULL]);   
                         }

                     if ($checkTypeOfPRomoCode->allowed_multiple_times == 'once') {  
                           $checkIfAlreadyApplied = DB::table('applied_pomo_codes') 
                                 ->where('userId', '=', $userId)
                                 ->where('promo_code', '=', $promo_code)
                                 ->first();

                         if ($checkIfAlreadyApplied) {
                             return response()->json(['statusCode' => '0', 'statusMessage' => 'You have already Applied this Promo Code', 'Result' => NULL]);   
                         }   

                      } 
             
           }          
           $up = DB::table('promo_codes')
                ->where('promo_code','=',$promo_code) 
                ->first(); 
            if ($up){
               return response()->json(['statusCode' => '1', 'statusMessage' => 'showing promo_code disocunt', 'Result' => $up]);
            }
            else
            { 
              return response()->json(['statusCode' => '0', 'statusMessage' => 'Not Valid promo', 'Result' => $up]);
            }    
        
    
       }
 
 /*
    public function verifyPromo(Request $request)
    {   
      
        $promo_code = $request->input('promo_code');
          
        $mytime = Carbon\Carbon::now();
        // return $mytime->toDateTimeString();

         $up = DB::table('promo_codes')
                ->where('promo_code','=',$promo_code) 
                ->first(); 
            if ($up){
               return response()->json(['statusCode' => '1', 'statusMessage' => 'showing promo_code disocunt', 'Result' => $up]);
            }
            else
            { 
              return response()->json(['statusCode' => '0', 'statusMessage' => 'Not Valid promo', 'Result' => $up]);
            }    
        }    
    */
  }
