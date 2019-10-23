<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Log;

class MonitorTest extends TestCase
{

    // use RefreshDatabase;
    // Don't use "RefreshDatabase" on production

    /**
     * Ensures that the event received is saved in the database.
     *
     * @test
     */
    public function saves_json_in_db()
    {

        $response = $this->post('/', [
            "title" => "Titulo",
            "from" => "Auth",
            "extra" => [
                "email" => "test@asd.com",
                "password" => "*****"
            ],
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
     * Ensures that the event received is saved in the log.
     *
     * @test
     */
    public function saves_json_in_monolog()
    {

        $response = $this->post('/', [
            "title" => "Titulo",
            "from" => "Auth",
            "extra" => [
                "email" => "test@asd.com",
                "password" => "*****"
            ],
            "config" => [
                "db" => 0,
                "file" => 1,
                "level" => "warning",
            ]
        ]);

        $response->assertOk();
    }

    /**
     * Ensures that the log is sent to the frontend.
     *
     * @test
     */

    public function get_info_about_logs()
    {

        $response = $this->post('/', [
            "title" => "Titulo",
            "from" => "Auth",
            "extra" => [
                "email" => "test@asd.com",
                "password" => "*****"
            ],
            "config" => [
                "db" => 1,
                "file" => 1,
                "level" => "warning",
            ]
        ]);

        $response = $this->get('/?date=2019-10-21');


        $response->assertJsonFragment([
            'title' => 'Titulo',
        ]);
    }
}
