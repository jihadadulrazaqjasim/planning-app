<?php

namespace Tests\Feature;

use App\Http\Resources\TaskResource;
use App\Models\Label;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeveloperRoleTest extends TestCase
{
    use RefreshDatabase;
    private User $developer;
    private User $tester;
    private User $owner;

    protected function setUp(): Void
    {
        parent::setUp();
        
        $this->artisan('passport:install');

        $this->developer = $this->createUser(type: 'developer');
        $this->tester = $this->createUser(type: 'tester');
        $this->owner = $this->createUser(type: 'owner');
    }

    private function header(User $user)
    {
        return ['Accept' => 'application/json' , 'Authorization' => 'Bearer '.$this->createUserToken(user: $user)];    
    }

    private function createUserToken(User $user)
    {
        return $user->createToken('PlanningWebsiteProject')->accessToken;
    }
    
    private function createUser(string $type)
    {
        return User::factory()->create([
            'type' => $type
        ]);
    }


    public function  test_show_all_developer_task_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        Task::factory(10)->create([
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);
        
       $response = $this->getJson('/api/developer/task', $this->header(user: $this->developer));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'message' => 'Task retrieved successfully for the developer!'
        ]);
        $this->assertDatabaseCount('tasks', 10);
    }

    public function  test_show_developer_task_by_sort_title_asc_or_desc_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        $array_char = ['aa','bb','cc','dd','g'];
        $task1 = Task::factory()->create([
            'title'=> 'task1',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);
        Task::factory(3)->create([
            'title'=> 'task2',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);

        $data = [
            'sort_title' => 'desc',
        ];
        
        $response = $this->json('GET','/api/developer/task',$data, $this->header(user: $this->developer),$data );

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson([
            'message' => 'Task retrieved successfully for the developer!'
        ]);
        $this->assertEquals('task1',Task::latest()->first()->title);
    }

    public function  test_search_for_task_by_developer_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');

        Task::factory()->create([
            'title'=> 'task1',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);
        Task::factory()->create([
            'title'=> 'task2',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);

        Task::factory()->create([
            'title'=> 'task3',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);

        $data = [
            'search' => 'k1',
        ];
        
        $response = $this->json('GET','/api/developer/task',$data, $this->header(user: $this->developer),$data );

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson([
            'data'=>[
                ['title' => 'task1']
            ],
            'message' => 'Task retrieved successfully for the developer!'
        ]);
    }

    
    public function  test_fetch_task_using_label_filtre_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');

        $task = Task::factory(10)->create([
            'title'=> 'task1',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);
        Task::factory(5)->create([
            'title'=> 'task2',
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);

        $task_id = $task->pluck('id')->toArray();
        foreach ($task_id as $id) {
            Label::factory()->create([
                'title' => 'family',
                'task_id' => $id, 
            ]);                
        }

        Label::factory(22)->create([
            'title' => 'important',
        ]);

        $data = [
            'filter_label' => 'family',
        ];
        
        $response = $this->json('GET','/api/developer/task',$data, $this->header(user: $this->developer),$data );

        $response->assertStatus(200);
        $response->assertJsonCount(3);

        $task_id = Label::where('title', 'like', '%family%')->get('task_id');
            
        $this->assertCount(10,Task::whereIn('id',$task_id)->get());
    }

    public function  test_show_all_developer_task_by_unauhtorized_user_return_error()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        Task::factory(10)->create([
            'user_id' => $this->developer->id,
            'image' => $image,
        ]);
        
       $response = $this->getJson('/api/developer/task', $this->header(user: $this->owner));

        $response->assertStatus(401);
    }
}
