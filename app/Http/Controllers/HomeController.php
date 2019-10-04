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
        
        $data = str_replace(" ", ' ', $data);


        switch($data["config"]["level"]){
            case "emergency": 
                Logs::channel("monitor")->emergency($data["title"], $data);
                break;
            case "alert": 
                Logs::channel("monitor")->alert($data["title"], $data);
                break;
            case "critical": 
                Logs::channel("monitor")->critical($data["title"], $data);
                break;
            case "error": 
                Logs::channel("monitor")->error($data["title"], $data);
                break;
            case "warning": 
                Logs::channel("monitor")->warning($data["title"], $data);
                break;
            case "notice": 
                Logs::channel("monitor")->notice($data["title"], $data);
                break;
            case "info": 
                Logs::channel("monitor")->info($data["title"], $data);
                break;
            default: 
                Logs::channel("monitor")->debug($data["title"], $data);
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


        $db_log = Log::where("created_at", "LIKE", "$date%")->get();
        $file_log = file(storage_path().'\logs\events-'. $date . '.log');

        $responseFile = [];


        foreach ($file_log as $file_line){
            $explodedLog = explode(" " , $file_line);


            $responseFile[$explodedLog[2]][] = ["date" => $explodedLog[0] . $explodedLog[1],
                                        "title" => $explodedLog[3],
                                        "data" => json_decode($explodedLog[4])
            ];

            break;
        }

        foreach($db_log as $db_line){
            $db_line->data = json_decode($db_line->data);
            $responseDb[$db_line->level] = $db_line;
        }


        return response()->json(["db"=>$responseDb, 
                                "file"=>$responseFile]);
    }
}
