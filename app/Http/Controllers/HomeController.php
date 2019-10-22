<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FileLog;
use App\Log;
use Storage;

class HomeController extends Controller
{
    public function index(Request $request)
    {

        $data = $request->all();
        $data['url'] = $request->url();
        $data['method'] = $request->method();
        $data['ip'] = $request->ip();


        if ($data["config"]["db"]) {
            $this->saveDB($data);
        }

        if ($data["config"]["file"]) {
            $this->saveFile($data);
        }

        return response(200);
    }

    public function saveFile($data)
    {



        $data = str_replace(" ", " ", $data);
        $data["extra"] = str_replace(" ", " ", $data["extra"]);
        // Replaces normal space with $nbsp;
        // First parameter is normal space, second one is %nbsp; in ascii (alt+255)

        switch ($data["config"]["level"]) {
            case "emergency":
                FileLog::channel("monitor")->emergency($data["title"], $data);
                break;
            case "alert":
                FileLog::channel("monitor")->alert($data["title"], $data);
                break;
            case "critical":
                FileLog::channel("monitor")->critical($data["title"], $data);
                break;
            case "error":
                FileLog::channel("monitor")->error($data["title"], $data);
                break;
            case "warning":
                FileLog::channel("monitor")->warning($data["title"], $data);
                break;
            case "notice":
                FileLog::channel("monitor")->notice($data["title"], $data);
                break;
            case "info":
                FileLog::channel("monitor")->info($data["title"], $data);
                break;
            default:
                FileLog::channel("monitor")->debug($data["title"], $data);
                break;
        }
    }

    public function saveDB($data)
    {
        Log::create([
            "title" => $data['title'],
            "from" => $data['from'],
            "url" => $data['url'],
            "method" => $data['method'],
            "extra" => json_encode($data['extra']),
            "ip" => $data['ip'],
            "level" => $data['config']['level'],
        ]);
    }


    public function show(Request $request)
    {

        $data = $request->all();
        $date = $data['date'];


        $db_log = Log::where("created_at", "LIKE", "$date%")->get();
        $file_log = file(storage_path() . '\logs\events-' . $date . '.log');

        $responseFile = [];


        foreach ($file_log as $file_line) {
            $explodedLog = explode(" ", $file_line);

            // Normalizes the 'level' atribute to be the same as in the DB
            $level = strtolower(preg_replace(['/local./', '/:/'], "", $explodedLog[2]));

            $responseFile[$level][] = [
                "date" => $explodedLog[0] . $explodedLog[1],
                "title" => $explodedLog[3],
                "data" => json_decode($explodedLog[4])
            ];
        }


        foreach ($db_log as $db_line) {
            $db_line->extra = json_decode($db_line->extra);
            $responseDb[$db_line->level][] = $db_line;
        }


        return response()->json([
            "db" => $responseDb,
            "file" => $responseFile
        ]);
    }
}
