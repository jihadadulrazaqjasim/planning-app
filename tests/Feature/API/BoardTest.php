<?php

namespace Tests\Feature\API;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BoardTest extends TestCase
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


    public function test_diplay_all_owner_board_successful()
    {
        $response = $this->actingAs($this->owner)->getJson('/api/boards',$this->header($this->owner));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success' ,
            'data' => [
                '*'=>[
                'id',
                'title',
                'description',
                'user_id',
                'created_at',
                'updated_at',
            ] ],
            'message'           
        ]);

    }

    public function test_diplay_all_owner_board_that_belong_to_him_successful()
    {
        Board::factory(5)->create([
            'user_id' => $this->owner->id,
        ]);
        Board::factory(2)->create();

        $response = $this->actingAs($this->owner)->getJson('/api/boards',$this->header($this->owner));

        $response->assertStatus(200);
        $response->assertJsonCount(5,'data');

    }
    
    public function test_diplay_all_owner_board_unauthorized_as_owner_successful()
    {
        $response = $this->actingAs($this->owner)->getJson('/api/boards',$this->header($this->developer));

        $response->assertStatus(401);

    }

    public function test_create_board_by_owner_successful()
    {
        $data =[
            'title' => 'planning website v1'    
        ];

        $response = $this->actingAs($this->owner)->postJson('/api/boards', $data, $this->header($this->owner));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'planning website v1',
                'user_id' => $this->owner->id,
            ],
            'message' => 'The Borad has been created successfully!'            
        ]);

    }

    public function test_create_board_by_someone_not_owner_successful()
    {
        $data =[
            'title' => 'planning website v1'    
        ];

        $response = $this->postJson('/api/boards', $data, $this->header($this->tester));

        $response->assertStatus(401);
    }

    public function test_create_board_for_owner_with_validate_error_successful()
    {
        $data =[
            'title' => ''    
        ];

        $response = $this->actingAs($this->owner)->postJson('/api/boards', $data, $this->header($this->owner));

        $response->assertStatus(422);
        $response->assertJsonCount(2);
        $response->assertJson([
            'message' => 'The title field is required.',
            'errors' => [
                'title' => ['The title field is required.'],
            ],
        ]);
    }

    public function test_delete_board_successful()
    {
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $response = $this->deleteJson('/api/boards/'.$board->id, [], $this->header($this->owner));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'The Board has been deleted successfully!'    
        ]);
    }

    public function test_delete_board_by_unauthorized_user_successful()
    {
        $board = Board::factory()->create();

        $response = $this->actingAs($this->owner)->deleteJson('/api/boards/'.$board->id, [], $this->header($this->owner));

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this process',
        ]);
    }

    public function test_update_board_by_authorized_owner_successful()
    {        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);
        $data = [
            'title' => 'borad updated',
            'description' => 'test update board by using feature testing',
        ];

        $response = $this->putJson('/api/boards/'.$board->id, $data, $this->header($this->owner));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_update_unauthorized_board_by_owner_successful()
    {        
        $board = Board::factory()->create();
        $data = [
            'title' => 'borad updated',
            'description' => 'test update board by using feature testing',
        ];

        $response = $this->putJson('/api/boards/'.$board->id, $data, $this->header($this->owner));
// dd($response->json());

        $response->assertStatus(403);
        $response->assertJsonCount(2);
        $response->assertJson([
            'success' => false,
            'data' => 'unauthorized to make this process',
        ]);
    }

    public function test_update_board_by_owner_validate_error_successful()
    {        
        $board = Board::factory()->create([
            'user_id' => $this->owner->id,
        ]);

        $data = [
            'title' => '',
        ];

        $response = $this->putJson('/api/boards/'.$board->id, $data, $this->header($this->owner));

        $response->assertStatus(422);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'message' => 'The title field is required.',
        ]);
    }
}