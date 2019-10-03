<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Log;

class MonitorTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @test
     */
    public function saves_json_in_db()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/', [
            "title" => "Titulo",
            "from" => "Auth",
            "url" => "/login",
            "method" => "post",
            "data" => [
                "email" => "test@asd.com",
                "password" => "*****"
            ],
            "ip" => "123.123.123.2",
            "config" => [
                "db" => 1,
                "file" => 1,
                "level" => "warning",
            ]
        ]);

        $response->assertStatus(200);

        $this->assertCount(1, Log::all());

    }


    /**
     * A basic feature test example.
     *
     * @test
     */
    public function saves_json_in_monolog()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/', [
            "title" => "Titulo",
            "from" => "Auth",
            "url" => "/login",
            "method" => "post",
            "data" => [
                "email" => "test@asd.com",
                "password" => "*****"
            ],
            "ip" => "123.123.123.2",
            "config" => [
                "db" => 0,
                "file" => 1,
                "level"=> "warning",
            ]
        ]);

        $response->assertOk();


    }

    /**
     * A basic feature test example.
     *
     * @test
     */

    public function get_info_about_logs()
    {
       $this->withoutExceptionHandling();

       $response = $this->post('/', [
        "title" => "Titulo",
        "from" => "Auth",
        "url" => "/login",
        "method" => "post",
        "data" => [
            "email" => "test@asd.com",
            "password" => "*****"
        ],
        "ip" => "123.123.123.2",
        "config" => [
            "db" => 1,
            "file" => 1,
            "level"=> "warning",
        ]
    ]);

       $response = $this->get('/?date=2019-10-03');


       $response->assertJsonFragment([
           'title' => 'Titulo',
    ]);
    }
}
