<?php

namespace Tests\Classes;

use Classes\Redirections;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WP_Post;
use WP_Post_Type;

class RedirectionsTest extends TestCase
{
    private const HOME_URL = 'https://volvocars.com/';
    private const BLOG_ID = 2;
    private const POST_ID = 1;
    private const VALID_POST_TYPE = 'stock-car';
    private const STATUS_DRAFT = 'draft';
    private const STATUS_PUBLISH = 'publish';

    private $redirectionsMock;

    public function setUp(): void
    {
        parent::setUp();

        WP_Mock::userFunction('add_action');

        $this->redirectionsMock = $this->getMockBuilder(Redirections::class)
            ->onlyMethods(['update_redirects', 'delete_redirects'])
            ->getMock();
    }

    public function testPrePostUpdate_unpublish()
    {
        $post_name = $new_post_name = 'unchanged';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_DRAFT
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_PUBLISH, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->once();

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects');

        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);

        $this->assertActionsCalled();
    }

    public function testPrePostUpdate_publish()
    {
        $post_name = $new_post_name = 'unchanged';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_PUBLISH
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_DRAFT, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->never();

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects');

        $this->redirectionsMock->expects($this->once())
            ->method('delete_redirects');


        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);

        $this->assertActionsCalled();
    }

    public function testPrePostUpdate_changeSlug_onPublished()
    {
        $post_name = 'old_name';
        $new_post_name = 'new_name';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_PUBLISH
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_PUBLISH, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->once();

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects');

        $this->redirectionsMock->expects($this->once())
            ->method('delete_redirects');

        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);

        $this->assertActionsCalled();
    }

    public function testPrePostUpdate_changeSlug_onDraft()
    {
        $post_name = 'old_name';
        $new_post_name = 'new_name';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_DRAFT
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_DRAFT, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->never();

        $this->redirectionsMock->expects($this->never())
            ->method('update_redirects');

        $this->redirectionsMock->expects($this->never())
            ->method('delete_redirects');


        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);
    }

    public function testPrePostUpdate_changeSlug_publish()
    {
        $post_name = 'old_name';
        $new_post_name = 'new_name';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_PUBLISH
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_DRAFT, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->never();

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects');

        $this->redirectionsMock->expects($this->once())
            ->method('delete_redirects');

        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);

        $this->assertActionsCalled();
    }

    public function testPrePostUpdate_changeSlug_unPublish()
    {
        $post_name = 'old_name';
        $new_post_name = 'new_name';
        $new_data = [
            'post_name' => $new_post_name,
            'post_status' => self::STATUS_DRAFT
        ];
        $post_object = (object) ['post_name' => $post_name, 'post_status' => self::STATUS_PUBLISH, 'post_type' => self::VALID_POST_TYPE];

        $this->prePostCommonPart($post_name, $post_object);

        WP_Mock::userFunction('add_row')
            ->once();

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects');

        $this->redirectionsMock->expects($this->never())
            ->method('delete_redirects');

        $this->redirectionsMock->pre_post_update(self::POST_ID, $new_data);

        $this->assertActionsCalled();
    }

    public function testPostNotVisible()
    {
        $blog_id = 2;
        $post_name = 'post_name';
        $post_type = 'stock-car';
        $type_rewrite_slug = 'dostepne-na-miejscu';
        $post_url = self::HOME_URL . $type_rewrite_slug . '/' . $post_name;
        $post_type_url = self::HOME_URL . $type_rewrite_slug . '/';

        $post_mock = $this->getMockBuilder(WP_Post::class)->getMock();
        $post_mock->post_name = $post_name;
        $post_mock->post_type = $post_type;

        $type_mock = $this->getMockBuilder(WP_Post_Type::class)->getMock();
        $type_mock->rewrite['slug'] = $type_rewrite_slug;

        WP_Mock::userFunction('get_post_type_object')
            ->once()
            ->with($post_type)
            ->andReturn($type_mock);

        WP_Mock::userFunction('get_current_blog_id')
            ->twice()
            ->andReturn($blog_id);

        WP_Mock::userFunction('get_home_url')
        ->twice()
        ->withAnyArgs()
        ->andReturnValues([$post_url, $post_type_url]);

        $this->redirectionsMock->expects($this->once())
            ->method('update_redirects')
            ->with('target', $post_type_url, 'source', $post_url);

        $this->redirectionsMock->post_not_visible(self::POST_ID, $post_mock);
    }

    protected function public(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    private function prePostCommonPart(string $post_name, object $post_object): void
    {
        $old_url = self::HOME_URL . self::VALID_POST_TYPE . '/' . $post_name;
        $new_url = self::HOME_URL . self::VALID_POST_TYPE . '/';

        WP_Mock::userFunction('get_post')
            ->andReturn($post_object);

        WP_Mock::userFunction('get_post_type_object')
            ->andReturn((object)['rewrite']);

        WP_Mock::userFunction('get_current_blog_id')
            ->twice()
            ->andReturn(self::BLOG_ID);

        WP_Mock::userFunction('get_home_url')
            ->twice()
            ->andReturnValues([$old_url, $new_url]);
    }
}
