<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serach_log;
use App\Models\User;
use App\Models\User_device;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SearchController extends Controller
{
 
    public $client;
    function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(['http://localhost:9200/'])
            ->build();
    }

    public function searchSinger(Request $request)
    {

        try {

            $params = [
                'index' => 'singers',
                'size' => 30,

                'body'  => [
                    'query' => [
                        'multi_match' => [
                            'query' => $request->input('search'),
                            'fuzziness' => 2,

                        ]
                    ]
                ]
            ];

            $data = $this->client->search($params);

            $response = [
                "error" => false,
                "total" => $data['hits']['total']['value'],
                "data" => $data['hits']['hits'],
            ];
            return $response;
        }catch (\Exception $e)
        {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return $response;
        }

    }

    public function searchLyricsByTitle(Request $request)
    {
        try {
            $params = [
                'index' => 'lyrics',
                'size' => 50,
                'body'  => [
                    'query' => [
                        'multi_match' => [
                            'query' => $request->input('search'),
                            'fields' => [ 'fulllyrics']

                        ]
                    ]
                ]
            ];
            $data = $this->client->search($params);
            $log = $this->logSearch(1,$request->input('search'), "web");


            $datap = $data['hits']['hits'];
            $lyric = array();
            $sarkisozu = array();

            foreach ($datap as $item) {

                foreach ($item['_source'] as $key => $val) {

                    if($key == "title")
                        $sarkisozu['title'] = addslashes($val);
                    if($key == "id")
                        $sarkisozu['online_id'] = $val;
                    if($key == "body")
                        $sarkisozu['body'] = addslashes($val);
                    if($key == "singer_name")
                        $sarkisozu['singer_name'] = addslashes($val);

             }
                array_push($lyric,$sarkisozu);
            }
            $response = [
                "error" => false,
                "total" => $data['hits']['total']['value'],
                "data" =>$lyric,
                'log' => $log


            ];
            return response()->json($response);
         
        }catch (\Exception $e)
        {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return  $response;
        }

    }

    public function logSearch($user_id,$data,$platform)
    {
        try {
           date_default_timezone_set("UTC");
           $id = md5($user_id).md5(time());
           $log = Serach_log::create([
               "id" => $id,
               "user_id" => $user_id,
               "ipAddr" => $_SERVER['REMOTE_ADDR'],
               "data" => $data,
               "platform" => $platform,
               "control" => 0
           ]);

            $response = [
                'error' => false,
                'messages' => $log
            ];
            return  $response;

        }catch (\Exception $e)
        {
            $response = [
                'error' => true,
                'messages' => $e->getMessage()
            ];
            return  $response;
        }
    }
}
