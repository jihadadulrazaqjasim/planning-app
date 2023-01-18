<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskTest extends TestCase
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


    public function test_create_new_task_by_owner_who_has_the_board_that_new_task_belong_to_it()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $data = [
            'title' => 'task1',
            'description' => 'create new test',
            'image' => $image,
            'due_date' => '13/02/23 02:01:12',
        ];

        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $response = $this->postJson('/api/tasks/'.$board->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'success',
            'data'=> [
                'title' ,
                'descriptoin' ,
                'image' ,
                'due_date',
                'current_status' ,
                'user_id' ,
                'board_id',
            ],
            'message'
        ]);

        $this->assertDatabaseHas('tasks',[
            'title' => 'task1',
            'description' => 'create new test',
            'due_date' => '2013-02-23',
            'user_id' => null,
            'board_id' => $board->id,
        ]);

        $lastTask = Task::latest()->first();
        $this->assertEquals($data['title'],$lastTask->title);
    }

    public function test_create_new_task_by_owner_who_has_not_the_board_that_new_task_belong_to_it()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $data = [
            'title' => 'task1',
            'description' => 'create new test',
            'image' => $image,
            'due_date' => '13/02/23 02:01:12',
        ];

        $board = Board::factory()->create();

        $response = $this->postJson('/api/tasks/'.$board->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this operation',
        ]);

    }
    
    public function test_create_new_task_by_unauthorized_user_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $data = [
            'title' => 'task1',
            'description' => 'create new test',
            'image' => $image,
            'due_date' => '13/02/23 02:01:12',
        ];

        $board = Board::factory()->create();

        $response = $this->postJson('/api/tasks/'.$board->id, $data, $this->header(user:$this->developer));

        $response->assertStatus(401);

    }

    public function test_create_new_task_by_invalid_data_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $data = [
            'title' => '',
            'description' => 'create new test',
            'image' => $image,
            'due_date' => '13/02/23 02:01:12',
        ];

        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $response = $this->postJson('/api/tasks/'.$board->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(422);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'message' => 'The title field is required.',
        ]);
    }

        public function test_update_task_by_owner_that_belong_to_specific_board_and_the_borad_belong_to_owner_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $data = [
            'title' => 'update',
            'description' => 'update task that belong to specific',
            'image' => $image,
            'due_date' => '13/03/23 02:01:12',
        ];

        $response = $this->putJson('/api/tasks/'.$task->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'success',
            'data'=> [
                'title' ,
                'descriptoin' ,
                'image' ,
                'due_date',
                'current_status' ,
                'user_id' ,
                'board_id',
            ],
            'message'
        ]);

        $this->assertDatabaseHas('tasks',[
            'title' => 'update',
            'description' => 'update task that belong to specific',
            'due_date' => '2013-03-23',
            'board_id' => $board->id,
        ]);

        $lastTask = Task::orderBy('updated_at','asc')->first();
        $this->assertEquals($data['title'],$lastTask->title);
    }

    public function test_update_task_by_owner_who_has_not_the_board_that_task_belongs_to_board_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $data = [
            'title' => 'update',
            'description' => 'update task that belong to specific',
            'image' => $image,
            'due_date' => '13/03/23 02:01:12',
        ];

        $response = $this->putJson('/api/tasks/'.$task->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this process',
        ]);

    }
    
    public function test_update_task_by_unauthorized_user_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $data = [
            'title' => 'update',
            'description' => 'update task that belong to specific',
            'image' => $image,
            'due_date' => '13/03/23 02:01:12',
        ];

        $response = $this->putJson('/api/tasks/'.$task->id, $data, $this->header(user:$this->tester));

        $response->assertStatus(401);

    }

    public function test_update_task_by_invalid_data_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $data = [
            'title' => '',
            'description' => 'update task that belong to specific',
            'image' => $image,
            'due_date' => '13/03/23 02:01:12',
        ];

        $response = $this->putJson('/api/tasks/'.$task->id, $data, $this->header(user:$this->owner));

        $response->assertStatus(422);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'message' => 'The title field is required.',
        ]);
    }

    public function test_Delete_task_by_owner_that_belong_to_specific_board_and_the_borad_belong_to_owner_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $response = $this->deleteJson('/api/tasks/'.$task->id, [], $this->header(user:$this->owner));

        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'success',
            'data'=> [
                'title' ,
                'descriptoin' ,
                'image' ,
                'due_date',
                'current_status' ,
                'user_id' ,
                'board_id',
            ],
            'message'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'The Task has been deleted successfully!'    
        ]);

        $this->assertDatabaseCount('tasks',0);
    }

    public function test_Delete_task_by_owner_who_has_not_the_board_that_task_belongs_to_board_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $response = $this->deleteJson('/api/tasks/'.$task->id, [], $this->header(user:$this->owner));

        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this process',
        ]);

    }
    
    public function test_Delete_task_by_unauthorized_user_successful()
    {

        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        $response = $this->deleteJson('/api/tasks/'.$task->id, [], $this->header(user:$this->tester));

        $response->assertStatus(401);

    }

    public function test_view_all_task_belong_to_specific_board_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory(10)->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        Task::factory(3)->create();

        $response = $this->getJson('/api/tasks/'.$board->id, $this->header(user: $this->owner));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'title' ,
                    'descriptoin' ,
                    'image' ,
                    'due_date',
                    'current_status' ,
                    'user_id' ,
                    'board_id',
                ]
            ],
            'message' 
        ]);

        $this->assertDatabaseCount('tasks',13);
        $this->assertCount(10,Task::where('board_id',$board->id)->get());
    }

    public function test_unauthorized_user_view_all_task_belong_to_specifcic_board_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory(10)->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        Task::factory(3)->create();

        $response = $this->getJson('/api/tasks/'.$board->id, $this->header(user: $this->developer));

        $response->assertStatus(401);
    }

    public function test_unauthorized_owner_view_all_task_belong_to_specifcic_board_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory()->create();
        
        $task = Task::factory(10)->create([
            'board_id' => $board->id,
            'image' => $image,
        ]);

        Task::factory(3)->create();

        $response = $this->getJson('/api/tasks/'.$board->id, $this->header(user: $this->owner));

        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this operation',
        ]);
    }

}
