<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

use App\Models\Post;
use SebastianBergmann\FileIterator\Factory;

class PostTest extends TestCase
{
  use RefreshDatabase;

  // 未ログインのユーザーは投稿一覧ページにアクセスできない
  public function test_gest_cannot_access_posts_index()
  {
    //posts に GET でアクセスしたことにする
    //$thisは　Test\Testcaseのクラスのこと。その中にget()などのメソッドがある
    $response = $this->get(route('posts.index'));
    //「このレスポンスは、指定したURLにリダイレクトしているはずだよね？」
    //と**検証（アサート）**するメソッド。
    $response->assertRedirect(route('login'));
  }

  // ログイン済みのユーザーは投稿一覧ページにアクセスできる
  public function test_user_can_access_posts_index()
  {
    //ダミーユーザーを１人つくる
    $user = User::factory()->create();
    //そのユーザーの投稿データーをつくる
    $post = Post::factory()->create(['user_id' => $user->id]); 

    //そのユーザーでログインした状態で、/postsへアクセス
    $response = $this->actingAs($user)->get(route('posts.index'));

    //アクセスに成功したらコード200を基本返すので、かえって来たかの検証
    $response->assertStatus(200);

    //画面に文字が（タイトル）が表示されているか検証
    $response->assertSee($post->title);
  }

  //未ログインのユーザーは投稿詳細ページにアクセスできない
  public function test_guest_cannot_access_posts_show()
  {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    //ログインした状態（actingAs($user)->）を使わず、投函詳細へアクセス
    //$postのユーザーへ
    $response = $this->get(route('posts.show', $post));
    //loginページにリダイレクトされているかを検証(assert)
    $response->assertRedirect(route('login'));
  }

  //ログイン済みのユーザーは投稿詳細ページにアクセスできる
  public function test_user_can_access_posts_show(){
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id'=> $user->id]);

    //$userで作成したダミーユーザーで
    //ログインした状態で、/postsへ、そのユーザーの投稿詳細へアクセス
    $response = $this->actingAs($user)->get(route('posts.show', $post));

    //成功していればステータスコード200が返るので
    //それを検証している
    $response->assertStatus(200);

    //その後、$postのタイトルが画面に文字が表示されているか検証
    $response->assertSee($post->title);

  }

  //未ログインのユーザーは新規投函ページにアクセスできない
  public function test_guest_cannot_access_posts_create(){
    $response = $this->get(route('posts.create'));
    $response->assertRedirect(route('login'));
  }

  //ログイン済みのユーザーは新規投稿ページへアクセスできる
  public function test_user_can_access_posts_create(){
    //ダミーのユーザーを1人作成
    $user = User::factory()->create();

    //actingAs($user)->でログイン状態を作り出し、/posts/createへアクセス
    $response = $this->actingAs($user)->get(route('posts.create'));
    $response->assertStatus(200);
  }

  //未ログインのユーザーは投稿を作成できない
  public function test_guest_cannot_access_posts_store(){
    //$postに適当な情報を代入
    $post = [
      'title' => 'プログラミング学習1日目',
      'content' => '今日からプログラミング学習開始！頑張るぞ！'
    ];


    $response = $this->post(route('posts.store'),$post);

    $this->assertDatabaseMissing('posts',$post);
    $response->assertRedirect(route('login'));
  }

  //ログイン済みのユーザーは投稿を作成できる
  public function test_user_can_access_posts_store(){
    $user = User::factory()->create();
    $post =[
      'title' => 'プログラミング学習1日目',
      'content' => '今日からプログラミング学習開始！頑張るぞ！'
    ];

    $response = $this->actingAs($user)->post(route('posts.store'), $post);

    //posts テーブルの中に、今送信した $post と
    //同じ内容のレコードが本当に保存されているか？
    $this->assertDatabaseHas('posts', $post);
    $response->assertRedirect(route('posts.index'));
  }

  //未ログインのユーザーは投稿編集ページにアクセスできない
  public function test_guest_cannot_access_posts_edit(){
    $user = user::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('posts.edit', $post));

    $response->assertRedirect(route('login'));
  }

  //ログイン済みのユーザーは他人の投稿編集ページにアクセスできない
  public function test_user_can_access_posts_edit(){
    $user = User::factory()->create();
    $other_user = User::factory()->create();
    $others_post = Post::factory()->create(['user_id'=>$other_user->id]);

    $response = $this->actingAs($user)->get(route('posts.edit', $others_post));

    $response->assertRedirect(route('posts.index'));

  }
  //ログイン済みのユーザーは自身の投稿編集ページにアクセスできる
  public function test_user_can_access_own_posts_edit(){
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id'=>$user->id]);

    $response = $this->actingAs($user)->get(route('posts.edit', $post));

    $response->assertStatus(200);

  }

  //未ログインのユーザーは投稿を更新できない
  public function test_guest_cannot_update_post(){
    $user = User::factory()->create();
    $old_post = Post::factory()->create(['user_id'=>$user->id]);

    $new_post = [
      'title' => 'プログラミング学習1日目',
      'content' => '今日からプログラミング学習開始！頑張る！'
    ];

    //patchは更新ようのTestメソッド
    $response = $this->patch(route('posts.update', $old_post), $new_post);
    
    $this->assertDatabaseMissing('posts', $new_post);
    $response->assertRedirect(route('login'));

  }

  //ログイン済みのユーザーは他人の投稿を更新できない
  public function test_user_cannot_update_others_post(){
    $user = User::factory()->create();
    $other_user = User::factory()->create();
    $others_old_post = Post::factory()->create(['user_id'=>$other_user->id]);

    $new_post = [
      'title' => 'プログラミング学習1日目',
      'content' => '今日からプログラミング学習！がんばる'
    ];

    $response = $this->actingAs($user)->patch(route('posts.update',$others_old_post), $new_post);

    $this->assertDatabaseMissing('posts', $new_post);
    $response->assertRedirect(route('posts.index'));

  }

  //ログイン済みのユーザーは自身の投稿を更新できるよう
  public function test_user_can_update_own_post(){
    $user = User::factory()->create();
    $old_post = Post::factory()->create(['user_id'=>$user->id]);

    $new_post = [
      'title' => 'プログラミング学習1日目',
      'content' => '今日からプログラミング学習開始。頑張るぞ'
    ];

    $response = $this->actingAs($user)->patch(route('posts.update', $old_post),$new_post);
    
    $this->assertDatabaseHas('posts', $new_post);
    $response->assertRedirect(route('posts.show', $old_post));
  }

  //未ログインのユーザーは投稿を削除できない
  public function test_guest_cannot_destory_post(){
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id'=> $user->id]);

    $response = $this->delete(route('posts.destroy', $post));

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
    $response->assertRedirect(route('login'));
  }

  //ログイン済みのユーザーは他人の投稿を削除できない
  public function test_user_cannot_destory_others_post(){
    $user = User::factory()->create();
    $other_user = User::factory()->create();
    $others_post = Post::factory()->create(['user_id'=> $other_user->id]);

    $response = $this->actingAs($user)->delete(route('posts.destroy', $others_post));

    $this->assertDatabaseHas('posts', ['id'=> $others_post->id]);
    $response->assertRedirect(route('posts.index'));
  }

  //ログイン済みのユーザーは自身の投稿を削除できる
  public function test_user_cannot_destory_own_post(){
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id'=>$user->id]);

    $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

    $this->assertDatabaseMissing('posts',['id'=>$post->id]);
    $response->assertRedirect(route('posts.index'));

  }



}
