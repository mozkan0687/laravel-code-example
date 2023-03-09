<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User_category;
use App\Models\User_device;
use App\Models\User_lyric;
use App\Models\User_match;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
/* EXAMPLE POSTMAN
    POST: http:localhost:8000/api/sync
     {
       "user_matches_last_sync_time" : "2023-02-14 21:02:45",
       "user_lyrics_last_sync_time"  : "2023-02-14 21:02:45",
       "user_categories_last_sync_time" : "2021-02-14 21:02:45",
       "email" : "mail@mail.com"
       }
     
  */

    public function find_last_sync_time(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "user_matches_last_sync_time" => "required | date",
                "user_lyrics_last_sync_time" => "required | date",
                "user_categories_last_sync_time" => "required | date",
                "user_device_last_sync_time"    => "required | date",
                'email' => "required | email"
            ]);
            $find_user = User::where('email', $request->input('email'));

            if ($find_user->count() < 1) {
                throw new Exception("User not found");
            }
            $user_id = $find_user->first();


            if ($validator->fails()) {
                throw new Exception("The time format is wrong or null");
            }
        

            $get_user_matches_last_sync_time    =  $this->find_user_matches_last_sync_time($request->input('user_matches_last_sync_time'), $user_id->id);
            $get_user_lyrics_last_sync_time     = $this->find_user_lyrics_last_sync_time($request->input('user_lyrics_last_sync_time'), $user_id->id);
            $get_user_categories_last_sync_time  = $this->find_user_categories_last_sync_time($request->input('user_categories_last_sync_time'), $user_id->id);
            $response = [
                'error' => false,
                'messages' => "",
                'user_matches_last_sync_time' => $get_user_matches_last_sync_time->original,
                'user_lyrics_last_sync_time' => $get_user_lyrics_last_sync_time->original,
                'user_categories_last_sync_time' => $get_user_categories_last_sync_time->original
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
    public function find_user_matches_last_sync_time($time, $user_id)
    {
        try {
            $get_user_matches_last_sync_time  = User_match::where('user_id', $user_id)->orderBy('updated_at', 'desc');
            if ($get_user_matches_last_sync_time->count() < 1) {
                throw new Exception("nodata");
            }
            $get_user_matches_last_sync_time = $get_user_matches_last_sync_time->first();
            $update_status = "uptodate";
            $data = "";
            $web_date = $get_user_matches_last_sync_time->updated_at;
            if ($get_user_matches_last_sync_time->updated_at > $time) {
                $update_status = "updatetodevice";
                $data = User_match::where('user_id', $user_id)->get();
            } else if ($get_user_matches_last_sync_time->updated_at < $time) {
                $update_status = "updatetoweb";
            } else {
                $update_status = "uptodate";
            }
            $response = [
                'update_status' => $update_status,
                'web_date'      => $web_date,
                'data'          => $data,
                'error' => false

            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()

            ];
            return response()->json($response, 400);
        }
    }
    public function find_user_lyrics_last_sync_time($time, $user_id)
    {
        try {
            $data = "";
            $get_user_lyrics_last_sync_time  = User_lyric::where('user_id', $user_id)->orderBy('updated_at', 'desc');
            if ($get_user_lyrics_last_sync_time->count() < 1) {
                throw new Exception("nodata");
            }
            $get_user_lyrics_last_sync_time = $get_user_lyrics_last_sync_time->first();
            $update_status = "uptodate";
            $web_date = $get_user_lyrics_last_sync_time->updated_at;
            if ($get_user_lyrics_last_sync_time->updated_at > $time) {
                $update_status = "updatetodevice";
                $data = User_lyric::where('user_id', $user_id)->get();
            } else if ($get_user_lyrics_last_sync_time->updated_at < $time) {
                $update_status = "updatetoweb";
            } else {
                $update_status = "uptodate";
            }
            $response = [
                'update_status' => $update_status,
                'web_date'      => $web_date,
                'error' => false,
                'data'  => $data,
                'time' => $time
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
    public function find_user_categories_last_sync_time($time, $user_id)
    {
        try {
            $data = "";
            $get_user_categories_last_sync_time  = User_category::where('user_id', $user_id)->orderBy('updated_at', 'desc');
            if ($get_user_categories_last_sync_time->count() < 1) {
                throw new Exception("nodata");
            }
            $get_user_categories_last_sync_time = $get_user_categories_last_sync_time->first();
            $update_status = "uptodate";
            $web_date = $get_user_categories_last_sync_time->updated_at;
            if ($get_user_categories_last_sync_time->updated_at > $time) {
                $update_status = "updatetodevice";
                $data = User_category::where('user_id', $user_id)->get();
            } else if ($get_user_categories_last_sync_time->updated_at < $time) {
                $update_status = "updatetoweb";
            } else {
                $update_status = "uptodate";
            }
            $response = [
                'update_status' => $update_status,
                'web_date'      => $web_date,
                'data'  => $data,
                'error' => false
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
    public function find_user_device_last_sync_time($user_id, $device){
        try{
            $find_user_device_last_time = User_device::where(['user_id' => $user_id , 'user_device' => $device]);
            if($find_user_device_last_time->count() > 0)
            {
                return  $find_user_device_last_time->first()->updated_at;
            }
            else
            {
                return "Cihaz yok";
                 yeni user device ekle
            }
        }catch (\Exception $e)
        {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }

    }
    /* BACKUP AND UPDATE START */
    public function backup_and_update_user_data(Request $request)
    {
        try{
            $user_match_status = "nodata";
            $user_lyric_status = "nodata";
            $user_category_status = "nodata";
            if($request->has('user_match'))
            {
                $match_data = $request->input('user_match');

                try{
                    foreach ($match_data as $item) {
                        $find = User_match::where([
                            ["user_id" , "=" , $item['user_id']],
                            ["category_id" , "=" , $item['category_id']]
                        ]);
                        if($find->count() == 1)
                        {
                            $find_id = $find->first();
                            $status = $this->update_match($find_id->id , $item);
                            $match_response[] = [

                                "process" => "update" ,
                                "status" => $status
                            ];
                        }
                        else
                        {
                            $status = $this->create_match($item);
                            $match_response[] = [
                                "process" => "create" ,
                                "status" => $status

                            ];
                        }
                        $user_lyric_status = $match_response;
                    }
                }
                catch(Exception $e)
                {
                    $response = [
                        'error' => true,
                        'messages' => $e->getMessage()
                    ];
                    return response()->json($response, 400);
                }
            }
            if($request->has('user_lyric'))
            {
                $lyric_data = $request->input('user_lyric');

                try{
                    foreach ($lyric_data as $item) {
                        $find = User_lyric::where([
                            ["user_id" , "=" , $item['user_id']],
                            ["user_lyric_id" , "=" , $item['user_lyric_id']]
                        ]);
                        if($find->count() == 1)
                        {
                            $find_id = $find->first();
                            $status = $this->update_lyric($find_id->id , $item);
                            $lyric_response[] = [

                                "process" => "update" ,
                                "status" => $status
                            ];
                        }
                        else
                        {
                            $status = $this->create_lyric($item);
                            $lyric_response[] = [
                                "process" => "create" ,
                                "status" => $status

                            ];
                        }
                        $user_lyric_status = $lyric_response;
                    }
                }
                catch(Exception $e)
                {
                    $response = [
                        'error' => true,
                        'messages' => $e->getMessage()
                    ];
                    return response()->json($response, 400);
                }
            }
            if($request->has('user_category'))
            {
                $data = $request->input('user_category');

                try{
                    foreach ($data as $item) {
                        $find = User_category::where([
                            ["user_id" , "=" , $item['user_id']],
                            ["category_id" , "=" , $item['category_id']]
                        ]);
                        if($find->count() == 1)
                        {
                            $find_id = $find->first();
                            $status = $this->update_category($find_id->id , $item);
                            $response[] = [

                                "process" => "update" ,
                                "status" => $status
                            ];
                        }
                        else
                        {
                            $status = $this->create_category($item);
                            $response[] = [
                                "process" => "create" ,
                                "status" => $status

                            ];
                        }
                        $user_category_status = $response;
                    }
                }
                catch(Exception $e)
                {
                    $response = [
                        'error' => true,
                        'messages' => $e->getMessage()
                    ];
                    return response()->json($response, 400);
                }

            }

            $response = [
                'user_match_status' => $user_match_status,
                'user_lyric_status' => $user_lyric_status,
                'user_category_status'  => $user_category_status
            ];
            return response()->json($response,200);

        }catch(Exception $e)
        {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
    public function update_category($id,$data)
    {
        try
        {
            $category = User_category::find($id);
            return $category->update($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }
    public function update_match($id,$data)
    {
        try
        {
            $category = User_match::find($id);
            return $category->update($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }
    public function update_lyric($id,$data)
    {
        try
        {
            $category = User_lyric::find($id);
            return $category->update($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }
    public function create_category($data)
    {
        try
        {

           return User_category::create($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }
    public function create_match($data)
    {
        try
        {
            return User_match::create($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }
    public function create_lyric($data)
    {
        try
        {
            return User_lyric::create($data);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }

    /*BACKUP AND UPDATE END*/

}
