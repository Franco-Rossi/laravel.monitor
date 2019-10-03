<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logs; 
use App\Log;
use Storage;

class HomeController extends Controller
{
    public function index(Request $request){

        $data = $request->all();

        if($data["config"]["db"]){
            $this->saveDB($data);
        }

        if($data["config"]["file"]){
            $this->saveFile($data);
        }

        return response(200);

    }

    public function saveFile($data){


        switch($data["config"]["level"]){
            case "emergency": 
                Logs::emergency($data["title"], $data);
                break;
            case "alert": 
                Logs::alert($data["title"], $data);
                break;
            case "critical": 
                Logs::critical($data["title"], $data);
                break;
            case "error": 
                Logs::error($data["title"], $data);
                break;
            case "warning": 
                Logs::warning($data["title"], $data);
                break;
            case "notice": 
                Logs::notice($data["title"], $data);
                break;
            case "info": 
                Logs::info($data["title"], $data);
                break;
            default: 
                Logs::debug($data["title"], $data);
                break;
        }

    }

    public function saveDB($data){
        Log::create([
            "title" => $data["title"],
            "from" => $data['from'],
            "url" => $data['url'],
            "method" => $data['method'],
            "data" => json_encode($data['data']),
            "ip" => $data['ip'],
            "level" => $data["config"]["level"],
        ]);
    }

    
    public function show(Request $request){

        $data = $request->all();
        $date = $data['date'];


        $logdb = Log::where("created_at", "LIKE", "$date%")->get();
        $logfile = file(storage_path().'\logs\laravel-'. $date . '.log');

        $response = [];


        foreach ($logfile as $log_line){
            $explodedLog = explode(" " , $log_line);

            $response[$explodedLog[2]][] = ["date" => $explodedLog[0] . $explodedLog[1],
                                        "title" => $explodedLog[3],
                                        "data" => json_decode($explodedLog[4])
            ];
            break;
        }

        foreach($logdb as $db_line){
            $db_line->data = json_decode($db_line->data);
            $responseDb[$db_line->level] = $db_line;
        }


        return response()->json(["db"=>$responseDb, 
                                "file"=>$response]);
    }
}
