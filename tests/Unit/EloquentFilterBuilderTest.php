<?php

namespace Marifyahya\EloquentFilter\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Marifyahya\EloquentFilter\Tests\TestCase;
use Marifyahya\EloquentFilter\Tests\Models\User;
use Marifyahya\EloquentFilter\Tests\Models\Post;
use Marifyahya\EloquentFilter\Tests\Models\Product;

class EloquentFilterBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    protected function createTables(): void
    {
        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('status')->default('active');
        });

        \Schema::create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('draft');
            $table->integer('views')->default(0);
            $table->unsignedInteger('user_id')->nullable();
            $table->date('created_at');
        });

        \Schema::create('products', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->default('active');
            $table->string('category')->nullable();
            $table->date('created_at');
            $table->softDeletes();
        });
    }

    #[Test]
    public function it_can_filter_by_exact_match()
    {
        User::create(['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@test.com', 'status' => 'active']);
        User::create(['firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@test.com', 'status' => 'inactive']);

        $result = User::filter(['status' => 'active'])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('John', $result->first()->firstname);
    }

    #[Test]
    public function it_can_search_by_keyword()
    {
        User::create(['firstname' => 'John', 'lastname' => 'Smith', 'email' => 'john@test.com', 'status' => 'active']);
        User::create(['firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@test.com', 'status' => 'active']);

        $result = User::filter(['search' => 'john'])->get();

        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_can_filter_by_date_range()
    {
        Post::create(['title' => 'Old Post', 'content' => 'lorem', 'status' => 'published', 'views' => 10, 'created_at' => '2023-01-01']);
        Post::create(['title' => 'New Post', 'content' => 'ipsum', 'status' => 'published', 'views' => 5, 'created_at' => '2024-06-01']);

        $result = Post::filter([
            'created_at_from' => '2024-01-01',
            'created_at_to' => '2024-12-31'
        ])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('New Post', $result->first()->title);
    }

    #[Test]
    public function it_can_sort_results()
    {
        Post::create(['title' => 'AAA Post', 'content' => 'aaa', 'status' => 'published', 'views' => 10, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'BBB Post', 'content' => 'bbb', 'status' => 'published', 'views' => 5, 'created_at' => '2024-01-02']);

        $result = Post::filter(['sort_by' => 'views', 'sort_dir' => 'desc'])->get();

        $this->assertEquals('AAA Post', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_with_operator()
    {
        Post::create(['title' => 'Popular', 'content' => 'x', 'status' => 'published', 'views' => 100, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Unpopular', 'content' => 'y', 'status' => 'published', 'views' => 5, 'created_at' => '2024-01-02']);

        $result = Post::filter(['views' => '>50'], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Popular', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_with_greater_than_or_equal_operator()
    {
        Post::create(['title' => 'Small', 'content' => 'x', 'status' => 'published', 'views' => 9, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Exact', 'content' => 'y', 'status' => 'published', 'views' => 10, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Large', 'content' => 'z', 'status' => 'published', 'views' => 11, 'created_at' => '2024-01-03']);

        $result = Post::filter(['views' => '>=10'], ['filterable' => ['id', 'status', 'user_id', 'views']])->pluck('title')->all();

        $this->assertEquals(['Exact', 'Large'], $result);
    }

    #[Test]
    public function it_can_filter_with_less_than_or_equal_operator()
    {
        Post::create(['title' => 'Small', 'content' => 'x', 'status' => 'published', 'views' => 9, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Exact', 'content' => 'y', 'status' => 'published', 'views' => 10, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Large', 'content' => 'z', 'status' => 'published', 'views' => 11, 'created_at' => '2024-01-03']);

        $result = Post::filter(['views' => '<=10'], ['filterable' => ['id', 'status', 'user_id', 'views']])->pluck('title')->all();

        $this->assertEquals(['Small', 'Exact'], $result);
    }

    #[Test]
    public function it_returns_all_when_no_filters()
    {
        User::create(['firstname' => 'User', 'lastname' => 'One', 'email' => 'u1@test.com', 'status' => 'active']);
        User::create(['firstname' => 'User', 'lastname' => 'Two', 'email' => 'u2@test.com', 'status' => 'inactive']);

        $result = User::filter()->get();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_filter_by_comma_separated_values()
    {
        Post::create(['title' => 'Draft Post', 'content' => 'x', 'status' => 'draft', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Published Post', 'content' => 'y', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Archived Post', 'content' => 'z', 'status' => 'archived', 'views' => 3, 'created_at' => '2024-01-03']);

        $result = Post::filter(['views' => '1,2'], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_filter_by_not_in_with_comma()
    {
        Post::create(['title' => 'Draft Post', 'content' => 'x', 'status' => 'draft', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Published Post', 'content' => 'y', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Archived Post', 'content' => 'z', 'status' => 'archived', 'views' => 3, 'created_at' => '2024-01-03']);

        $result = Post::filter(['views' => '!1,3'], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Published Post', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_by_not_in_with_operator()
    {
        Post::create(['title' => 'Draft Post', 'content' => 'x', 'status' => 'draft', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Published Post', 'content' => 'y', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['views' => '!=2'], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Draft Post', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_by_null()
    {
        Post::create(['title' => 'With User', 'content' => 'x', 'status' => 'published', 'views' => 1, 'user_id' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Without User', 'content' => 'y', 'status' => 'published', 'views' => 2, 'user_id' => null, 'created_at' => '2024-01-02']);

        $result = Post::filter(['user_id' => 'null'], ['filterable' => ['id', 'status', 'user_id']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Without User', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_by_not_null()
    {
        Post::create(['title' => 'With User', 'content' => 'x', 'status' => 'published', 'views' => 1, 'user_id' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Without User', 'content' => 'y', 'status' => 'published', 'views' => 2, 'user_id' => null, 'created_at' => '2024-01-02']);

        $result = Post::filter(['user_id' => '!null'], ['filterable' => ['id', 'status', 'user_id']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('With User', $result->first()->title);
    }

    #[Test]
    public function it_can_filter_by_between()
    {
        Post::create(['title' => 'Cheap', 'content' => 'x', 'status' => 'published', 'views' => 10, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Medium', 'content' => 'y', 'status' => 'published', 'views' => 50, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Expensive', 'content' => 'z', 'status' => 'published', 'views' => 100, 'created_at' => '2024-01-03']);

        $result = Post::filter(['views' => '<>20,80'], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Medium', $result->first()->title);
    }

    #[Test]
    public function it_can_use_custom_filter_method_on_model()
    {
        Post::create(['title' => 'Active Post', 'content' => 'a', 'status' => 'active', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Published Post', 'content' => 'b', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);
        Post::create(['title' => 'Draft Post', 'content' => 'c', 'status' => 'draft', 'views' => 3, 'created_at' => '2024-01-03']);

        $result = Post::filter(['status' => 'active_published'])->get();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_use_custom_filter_name_method_on_user()
    {
        User::create(['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@test.com', 'status' => 'active']);
        User::create(['firstname' => 'Jane', 'lastname' => 'Smith', 'email' => 'jane@test.com', 'status' => 'active']);

        $result = User::filter(['name' => 'john'])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('John', $result->first()->firstname);
    }

    #[Test]
    public function it_can_use_custom_filter_class()
    {
        Post::create(['title' => 'Long Title Here', 'content' => 'a', 'status' => 'published', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Short', 'content' => 'b', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['title' => 'custom'], [
            'filterable' => ['id', 'status', 'title'],
            'custom_filters' => [
                'title' => function ($query, $value) {
                    $query->where('title', 'LIKE', 'Long%');
                },
            ],
        ])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Long Title Here', $result->first()->title);
    }

    #[Test]
    public function it_can_use_filterable_map_alias()
    {
        Post::create(['title' => 'First Post', 'content' => 'x', 'status' => 'published', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Second Post', 'content' => 'y', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['post_id' => '1'])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('First Post', $result->first()->title);
    }

    #[Test]
    public function it_can_use_multi_column_filterable_map_alias()
    {
        User::create(['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@test.com', 'status' => 'active']);
        User::create(['firstname' => 'Jane', 'lastname' => 'Smith', 'email' => 'jane@test.com', 'status' => 'active']);

        $result = User::filter(['full_name' => 'smith'], [
            'filterable_map' => [
                'full_name' => ['firstname', 'lastname'],
            ],
        ])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Jane', $result->first()->firstname);
    }

    #[Test]
    public function it_can_filter_trashed_only()
    {
        Product::create(['id' => 1, 'name' => 'Active Product', 'status' => 'active', 'category' => 'a', 'created_at' => '2024-01-01']);
        Product::create(['id' => 2, 'name' => 'Deleted Product', 'status' => 'active', 'category' => 'b', 'created_at' => '2024-01-02']);
        Product::find(2)->delete();

        $result = Product::filter(['trashed' => 'only'])->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Deleted Product', $result->first()->name);
    }

    #[Test]
    public function it_can_filter_trashed_with()
    {
        Product::create(['id' => 1, 'name' => 'Active Product', 'status' => 'active', 'category' => 'a', 'created_at' => '2024-01-01']);
        Product::create(['id' => 2, 'name' => 'Deleted Product', 'status' => 'active', 'category' => 'b', 'created_at' => '2024-01-02']);
        Product::find(2)->delete();

        $result = Product::filter(['trashed' => 'with'])->get();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_filter_by_array_values()
    {
        Post::create(['title' => 'Draft', 'content' => 'x', 'status' => 'draft', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'Published', 'content' => 'y', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['views' => [1, 2]], ['filterable' => ['id', 'status', 'user_id', 'views']])->get();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_filter_sort_with_minus_prefix()
    {
        Post::create(['title' => 'A Post', 'content' => 'a', 'status' => 'published', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'B Post', 'content' => 'b', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['sort' => '-title'])->get();

        $this->assertEquals('B Post', $result->first()->title);
    }

    #[Test]
    public function it_ignores_sorting_by_non_sortable_fields()
    {
        Post::create(['title' => 'A Post', 'content' => 'a', 'status' => 'published', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'B Post', 'content' => 'b', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['sort_by' => 'content', 'sort_dir' => 'desc'])->get();

        $this->assertEquals('A Post', $result->first()->title);
    }

    #[Test]
    public function it_accepts_uppercase_sort_direction()
    {
        Post::create(['title' => 'A Post', 'content' => 'a', 'status' => 'published', 'views' => 1, 'created_at' => '2024-01-01']);
        Post::create(['title' => 'B Post', 'content' => 'b', 'status' => 'published', 'views' => 2, 'created_at' => '2024-01-02']);

        $result = Post::filter(['sort_by' => 'views', 'sort_dir' => 'DESC'])->get();

        $this->assertEquals('B Post', $result->first()->title);
    }

    #[Test]
    public function it_ignores_non_filterable_fields()
    {
        User::create(['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@test.com', 'status' => 'active']);

        $result = User::filter(['nonexistent' => 'value'])->get();

        $this->assertCount(1, $result);
    }
}
