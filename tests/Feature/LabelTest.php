<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Label;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LabelTest extends TestCase
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

    public function test_create_new_label_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'family',
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        Label::factory(32)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        $response = $this->postJson('/api/label/'.$task->id, $data , $this->header(user: $this->owner));
        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'title' => 'family',
            'task_id' => $task->id,
        ]);
        $this->assertCount(1,Label::where('task_id',$task->id)->get());
    }

    public function test_create_new_label_invalid_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => '',
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        Label::factory(32)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        $response = $this->postJson('/api/label/'.$task->id, $data , $this->header(user: $this->owner));
        
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'The title field is required.',
        ]);   
    }
    
    public function test_create_new_label_unauthorized_owner_return_error()
    {
        $board = Board::factory()->create([
            // 'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'family',
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        Label::factory(32)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        $response = $this->postJson('/api/label/'.$task->id, $data , $this->header(user: $this->owner));
        
        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'unauthorized '
        ]);
    }

        
    public function test_create_new_label_unauthorized_user_return_error()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'family',
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        Label::factory(32)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        $response = $this->postJson('/api/label/'.$task->id, $data , $this->header(user: $this->tester));
        
        $response->assertStatus(401);
    }
    
    public function test_show_label_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'id' => $task->id, 
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        $label = Label::factory(3)->create([
            'task_id' => $task->id,
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        Label::factory(3)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);
       
        $response = $this->getJson('/api/label/'.$task->id , $this->header(user: $this->owner));
        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'task_id' => $task->id,
        ]);
        $this->assertCount(3,Label::where('task_id',$task->id)->get());
    }
   
    public function test_show_label_unauthorized_owner_successful()
    {
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'id' => $task->id, 
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        $label = Label::factory(3)->create([
            'task_id' => $task->id,
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        Label::factory(3)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        $response = $this->getJson('/api/label/'.$task->id , $this->header(user: $this->owner));
       
        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'unauthorized '
        ]);
    }
     
    public function test_show_label_unauthorized_user_successful()
    {
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'id' => $task->id, 
        ];

        $array_title = ['family',
                'important',
                'work related',
                'personal',
            ];

        $label = Label::factory(3)->create([
            'task_id' => $task->id,
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);

        Label::factory(3)->create([
            'title' => function() use($array_title){
                return $array_title[array_rand($array_title)];
            },
        ]);
        
        $response = $this->getJson('/api/label/'.$task->id , $this->header(user: $this->developer));
       
        $response->assertStatus(401);
    }

    public function test_update_label_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'important',
        ];

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->patchJson('/api/label/'.$label->id, $data , $this->header(user: $this->owner));
        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'title' => 'important',
            'task_id' => $task->id,
        ]);
        $this->assertCount(1,Label::where('task_id',$task->id)->where('title','like','important')->get());
    }
  
    public function test_update_label_unauthorized_owner_return_error()
    {
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'important',
        ];

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->patchJson('/api/label/'.$label->id, $data , $this->header(user: $this->owner));
       
        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'unauthorized '
        ]);   
    }

    public function test_update_label_unauthorized_user_return_error()
    {
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $data = [
            'title' => 'important',
        ];

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->patchJson('/api/label/'.$label->id, $data , $this->header(user: $this->tester));
       
        $response->assertStatus(401); 
    }

    public function test_delete_label_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->deleteJson('/api/label/'.$label->id,[] ,$this->header(user: $this->owner));
       
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'task_id' => $task->id,
        ]);
        $this->assertCount(0,Label::where('task_id',$task->id)->get());
    }
    
    public function test_delete_label_unauthorized_owner_successful()
    {
        $board = Board::factory()->create();
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->deleteJson('/api/label/'.$label->id,[] ,$this->header(user: $this->owner));
              
        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'success' => false,
            'data' => 'unauthorized '
        ]);
    }
  
    public function test_delete_label_unauthorized_user_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        
        $task = Task::factory()->create([
            'board_id' => $board->id,
        ]);

        $label = Label::factory()->create([
            'task_id' => $task->id,
            'title' => 'related work'
        ]);

        $response = $this->deleteJson('/api/label/'.$label->id,[] ,$this->header(user: $this->developer));
              
        $response->assertStatus(401);
    }
}