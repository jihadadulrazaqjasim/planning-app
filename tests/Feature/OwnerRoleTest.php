<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerRoleTest extends TestCase
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


    public function test_assign_task_to_developer_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' => 'to-do' 
        ]);

        $data = [
            'assign_id' => $this->developer->id,
        ];

        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
              'Task'=> [
                'id',
                'title',
                'descriptoin',
                'image',
                'due_date',
                'current_status',
                'user_id',
                'board_id',
                'created_at',
                'updated_at' ,
              ],
              'User' =>  [
                'id' ,
                'name',
                'type' ,
                'email' ,
                'created_at',
              ]
            ],
            "message"
            ]);

            $response->assertJsonFragment([
                'user_id' => $this->developer->id,
                'message' => 'The assign has been selected successfully!'
            ]);
    }
    
    public function test_assign_task_to_tester_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' => 'testing' 
            // function () {
            //    $array = [null,'to-do'];
            //     return $array[array_rand($array)];
            // },
        ]);

        $data = [
            'assign_id' => $this->tester->id,
        ];

        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
              'Task'=> [
                'id',
                'title',
                'descriptoin',
                'image',
                'due_date',
                'current_status',
                'user_id',
                'board_id',
                'created_at',
                'updated_at' ,
              ],
              'User' =>  [
                'id' ,
                'name',
                'type' ,
                'email' ,
                'created_at',
              ]
            ],
            "message"
            ]);

            $response->assertJsonFragment([
                'user_id' => $this->tester->id,
                'message' => 'The assign has been selected successfully!'
            ]);
    }

    public function test_assign_task_that_current_status_not_allowed_for_developer_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' =>  function () {
               $array = [null,'testing','dev-review','close','done'];
                return $array[array_rand($array)];
            },
        ]);
        $data = [
            'assign_id' => $this->developer->id,
        ];

        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(404);
        
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'It cannot be assigned to this developer. The current status: '.$task->current_status,
        ]);
    }
    
    public function test_assign_task_that_current_status_not_equal_testing_to_tester_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' =>  function () {
               $array = [null,'to-do','inprograss','dev-review','close','done'];
                return $array[array_rand($array)];
            },
        ]);
        $data = [
            'assign_id' => $this->tester->id,
        ];

        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(404);
        
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'It cannot be assigned to this Tester. The current status: '.$task->current_status,
        ]);
    }

    public function test_assign_task_invalid_id_failed_required_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $array = [$this->developer->id => 'to-do', $this->tester->id => 'testing'];
        $user_id = array_rand($array);
        $current_status = $array[$user_id];
        
        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' =>  $current_status,
        ]);
        
        $data = [
            'assign_id' => '',
        ];
        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(422);
        
        $response->assertJsonFragment([
            'message' => 'The assign id field is required.',
        ]);
    }

    public function test_assign_task_to_owner_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task =Task::factory()->create([
            'board_id' => $board->id,
            'current_status' => 'to-do' 
        ]);

        $data = [
            'assign_id' => $this->owner->id,
        ];

        $response = $this->postJson('/api/assign/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'It cannot be assigned to this user because he is owner '
        ]);
    }

    public function test_show_all_owner_task_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        Task::factory(20)->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $response = $this->getJson('/api/owner/task', $this->header(user: $this->owner));

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

        $this->assertDatabaseCount('tasks',50);
        $this->assertCount(20,Task::whereIn('board_id',$board_id)->get());
    }

    public function test_show_all_owner_task_unauthorized_return_error()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        Task::factory(20)->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $response = $this->getJson('/api/owner/task', $this->header(user: $this->developer));

        $response->assertStatus(401);
    }

    public function test_owner_change_status_of_specific_task_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);
        
        $array_status = array('to-do','in-progress','testing','dev-review','done','close');
        $data = [
            'change_status' => $array_status[array_rand($array_status)],
        ];

        $response = $this->patchJson('/api/owner/change-status/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(200);
    }

    public function test_owner_change_status_of_specific_task_invalid_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);
        
        $data = [
            'change_status' => '',
        ];

        $response = $this->patchJson('/api/owner/change-status/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(422);
    }
    
    public function test_owner_change_status_of_specific_task_by_selecting_same_status_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task = Task::factory()->create([
            'board_id' => $board->id,
            'current_status' => 'to-do',
        ]);
        
        $data = [
            'change_status' => 'to-do',
        ];

        $response = $this->patchJson('/api/owner/change-status/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'The current status value is same the change status!',
        ]);
    }

    public function test_owner_change_status_of_specific_task_by_selecting_undefined_status_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $task = Task::factory()->create([
            'board_id' => $board->id,
            'current_status' => 'to-do',
        ]);
        
        $data = [
            'change_status' => 'to-work',
        ];

        $response = $this->patchJson('/api/owner/change-status/'.$task->id, $data, $this->header(user: $this->owner));

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'There is no changes status like that ',
        ]);
    }

    public function test_show_all_logs_task_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        $task = Task::factory(5)->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        $task_unrelated = Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $task_id = $task->pluck('id')->toArray();
        $task_unrelated_id = $task_unrelated->pluck('id')->toArray();

        Status::factory(100)->create([
            'task_id' => function() use($task_id){
                return $task_id[array_rand($task_id)];
            }
        ]);

        Status::factory(220)->create([
            'task_id' => function() use($task_unrelated_id){
                return $task_unrelated_id[array_rand($task_unrelated_id)];
            }
        ]);

        $response = $this->getJson('/api/owner/task-logs', $this->header(user: $this->owner));

        $response->assertStatus(200);
        $this->assertDatabaseCount('statuses',320);
        $this->assertCount(100,Status::whereIn('task_id',$task_id)->get());
    }

    public function test_show_all_logs_for_specific_task_successful()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        //create board for owner with specific borad id to track this task
        $specific_board =Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        $task = Task::factory()->create([
            'board_id' => $specific_board->id,
            'image' => $image,
        ]);

        $task_author = Task::factory(12)->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        $task_unrelated = Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $task_id = $task_author->pluck('id')->toArray();
        $task_unrelated_id = $task_unrelated->pluck('id')->toArray();

        Status::factory(12)->create([
            'task_id' => $task->id,
        ]);

        Status::factory(22)->create([
            'task_id' => function() use($task_id){
                return $task_id[array_rand($task_id)];
            }
        ]);

        Status::factory(60)->create([
            'task_id' => function() use($task_unrelated_id){
                return $task_unrelated_id[array_rand($task_unrelated_id)];
            }
        ]);

        $response = $this->getJson('/api/owner/task-logs/'.$task->id, $this->header(user: $this->owner));

        $response->assertStatus(200);
        $this->assertDatabaseCount('statuses',94);
        $this->assertCount(12,Status::where('task_id',$task->id)->get());
    }

    public function test_show_all_logs_task_by_unauthorized_user_return_error()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        $task = Task::factory(5)->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        $task_unrelated = Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $task_id = $task->pluck('id')->toArray();
        $task_unrelated_id = $task_unrelated->pluck('id')->toArray();

        Status::factory(100)->create([
            'task_id' => function() use($task_id){
                return $task_id[array_rand($task_id)];
            }
        ]);

        Status::factory(220)->create([
            'task_id' => function() use($task_unrelated_id){
                return $task_unrelated_id[array_rand($task_unrelated_id)];
            }
        ]);

        $response = $this->getJson('/api/owner/task-logs', $this->header(user: $this->developer));

        $response->assertStatus(401);
    }

    public function test_show_all_log_for_specific_task_by_unauthorized_user_return_error()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        //create board for owner with specific borad id to track this task
        Board::factory()->create([
            'user_id' => $this->owner->id,
            'id' => '12',
        ]);

        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        $task = Task::factory()->create([
            'board_id' => '12',
            'image' => $image,
        ]);

        $task_author = Task::factory()->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        $task_unrelated = Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $task_id = $task_author->pluck('id')->toArray();
        $task_unrelated_id = $task_unrelated->pluck('id')->toArray();

        Status::factory(21)->create([
            'task_id' => $task->id,
        ]);

        Status::factory(22)->create([
            'task_id' => function() use($task_id){
                return $task_id[array_rand($task_id)];
            }
        ]);

        Status::factory(60)->create([
            'task_id' => function() use($task_unrelated_id){
                return $task_unrelated_id[array_rand($task_unrelated_id)];
            }
        ]);

        $response = $this->getJson('/api/owner/task-logs/'.$task->id, $this->header(user: $this->tester));

        $response->assertStatus(401);
    }

    public function test_show_all_log_for_specific_task_by_unauthorized_owner_return_error()
    {
        Storage::fake('task/image');
        $image = UploadedFile::fake()->image('taskImage.jpg');
        
        //create board for owner with specific borad id to track this task
        Board::factory()->create([
            'id' => '12',
        ]);

        $board = Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);

        $board_unauth = Board::factory(10)->create([
            'user_id' => User::factory(),
        ]);

        $board_id = $board->pluck('id')->toArray();
        $board_id_unauth = $board_unauth->pluck('id')->toArray();
        
        $task = Task::factory()->create([
            'board_id' => '12',
            'image' => $image,
        ]);

        $task_author = Task::factory()->create([
            'board_id' => function () use ($board_id){
                return $board_id[array_rand($board_id)];
            },
            'image' => $image,
        ]);

        $task_unrelated = Task::factory(30)->create([
            'board_id' => function() use($board_id_unauth){
                return $board_id_unauth[array_rand($board_id_unauth)];
            }
        ]);

        $task_id = $task_author->pluck('id')->toArray();
        $task_unrelated_id = $task_unrelated->pluck('id')->toArray();

        Status::factory(21)->create([
            'task_id' => $task->id,
        ]);

        Status::factory(22)->create([
            'task_id' => function() use($task_id){
                return $task_id[array_rand($task_id)];
            }
        ]);

        Status::factory(60)->create([
            'task_id' => function() use($task_unrelated_id){
                return $task_unrelated_id[array_rand($task_unrelated_id)];
            }
        ]);

        $response = $this->getJson('/api/owner/task-logs/'.$task->id, $this->header(user: $this->owner));

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'unauthorized to make this operation'
        ]);
    }

}
