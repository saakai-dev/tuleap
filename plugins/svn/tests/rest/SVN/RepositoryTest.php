<?php
/**
 * Copyright (c) Enalean, 2017. All rights reserved
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */

namespace Tuleap\SVN\REST;

use REST_TestDataBuilder;

require_once dirname(__FILE__).'/../bootstrap.php';

class RepositoryTest extends TestBase
{


    private function getResponseWithProjectMember($request)
    {
        return $this->getResponseByToken(
            $this->getTokenForUserName(REST_TestDataBuilder::TEST_USER_3_NAME),
            $request
        );
    }

    public function testGETRepositoryForProjectAdmin()
    {
        $response = $this->getResponse($this->client->get('svn/1'));

        $repository = $response->json();

        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($repository['name'], 'repo01');
        $this->assertArrayHasKey('settings', $repository);
        $this->assertEquals(
            $repository['settings']['commit_rules'],
            array(
                "is_reference_mandatory"           => false,
                "is_commit_message_change_allowed" => false
            )
        );

        $this->assertEquals(
            $repository['settings']['immutable_tags'],
            array(
                "paths"     => array(),
                "whitelist" => array(),
            )
        );

        $this->assertEquals(
            $repository['settings']['access_file'],
            "[/] * = rw @members = rw"
        );

        $this->assertEquals(
            $repository['settings']['email_notifications'],
            array(
                array(
                    'user_groups' => array(),
                    'users'       => array(),
                    'emails'      => array("project-announce@list.example.com", "project-devel@lists.example.com"),
                    'path'        => "/tags",
                ),
                array(
                    'user_groups' => array(),
                    'users'       => array(),
                    'emails'      => array("project-svn@list.example.com"),
                    'path'        => "/trunk"
                )
            )
        );
    }

    public function testGETRepositoryForProjectMember()
    {
        $response = $this->getResponseWithProjectMember($this->client->get('svn/1'));

        $repository = $response->json();

        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($repository['name'], 'repo01');
        $this->assertArrayNotHasKey('settings', $repository);
    }

    /**
     * @depends testGETRepositoryForProjectAdmin
     * @depends testGETRepositoryForProjectMember
     */
    public function testDELETERepositoryForProjectAdmin()
    {
        $response  = $this->getResponse($this->client->delete(
            'svn/1'
        ));

        $this->assertEquals($response->getStatusCode(), 202);
    }

    /**
     * @depends testGETRepositoryForProjectAdmin
     * @depends testGETRepositoryForProjectMember
     */
    public function testDELETERepositoryForProjectMember()
    {
        $this->setExpectedException('Guzzle\Http\Exception\ClientErrorResponseException');
        $response = $this->getResponseWithProjectMember(
            $this->client->delete(
                'svn/1'
            )
        );
        $this->assertEquals($response->getStatusCode(), 401);
    }

    public function testPOSTRepositoryForProjectAdmin()
    {
        $params = json_encode(
            array(
                "project_id" => $this->svn_project_id,
                "name"       => "my_repository",
            )
        );

        $response   = $this->getResponse($this->client->post('svn', null, $params));
        $repository = $response->json();

        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($repository['name'], 'my_repository');
        $this->assertEquals(
            $repository['settings']['commit_rules'],
            array(
                "is_reference_mandatory"           => false,
                "is_commit_message_change_allowed" => false
            )
        );
        $this->assertEquals(
            $repository['settings']['immutable_tags'],
            array(
                "paths"     => array(),
                "whitelist" => array(),
            )
        );
        $this->assertEquals(
            $repository['settings']['access_file'],
            ""
        );
    }

    public function testPOSTRepositoryForProjectAdminWithCustomSettings()
    {
        $params = json_encode(
            array(
                "project_id" => $this->svn_project_id,
                "name"       => "my_repository_02",
                "settings"   => array(
                    "commit_rules" => array(
                        "is_reference_mandatory"           => true,
                        "is_commit_message_change_allowed" => true
                    ),
                    "immutable_tags" => array(
                        "paths"      => array(
                            "/tags1",
                            "/tags2"
                        ),
                        "whitelist" => array(
                            "/white1",
                            "/white2"
                        )
                    ),
                    "access_file"         => "[/] * = rw\r\n@members = rw",
                    "email_notifications" => array(
                        array(
                            'user_groups' => array(),
                            'users'       => array(102, 103),
                            'emails'      => array("project-announce@list.example.com", "project-devel@lists.example.com"),
                            'path'        => "/tags",
                        ),
                        array(
                            'user_groups' => array(),
                            'users'       => array(),
                            'emails'      => array("project-svn@list.example.com"),
                            'path'        => "/trunk"
                        )
                    )
                )
            )
        );

        $response   = $this->getResponse($this->client->post('svn', null, $params));
        $repository = $response->json();

        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($repository['name'], 'my_repository_02');
        $this->assertEquals(
            $repository['settings']['commit_rules'],
            array(
                "is_reference_mandatory"           => true,
                "is_commit_message_change_allowed" => true
            )
        );
        $this->assertEquals(
            $repository['settings']['immutable_tags'],
            array(
                "paths"     => array('/tags1', '/tags2'),
                "whitelist" => array('/white1', '/white2'),
            )
        );
        $this->assertEquals(
            $repository['settings']['access_file'],
            "[/] * = rw\r\n@members = rw"
        );

        $user_102 = array(
            "id"           => 102,
            "uri"          => "users/102",
            "user_url"     => "/users/rest_api_tester_1",
            "real_name"    => "Test User 1",
            "display_name" => "Test User 1 (rest_api_tester_1)",
            "username"     => "rest_api_tester_1",
            "ldap_id"      => "tester1",
            "avatar_url"   => "http://localhost/themes/common/images/avatar_default.png",
            "is_anonymous" => false
        );

        $user_103 = array(
            "id"           => 103,
            "uri"          => "users/103",
            "user_url"     => "/users/rest_api_tester_2",
            "real_name"    => "",
            "display_name" => " (rest_api_tester_2)",
            "username"     => "rest_api_tester_2",
            "ldap_id"      => "",
            "avatar_url"   => "http://localhost/themes/common/images/avatar_default.png",
            "is_anonymous" => false
        );

        $this->assertEquals(
            $repository['settings']['email_notifications'],
            array(
                array(
                    'user_groups' => array(),
                    'users'       => array($user_102, $user_103),
                    'emails'      => array("project-announce@list.example.com", "project-devel@lists.example.com"),
                    'path'        => "/tags",
                ),
                array(
                    'user_groups' => array(),
                    'users'       => array(),
                    'emails'      => array("project-svn@list.example.com"),
                    'path'        => "/trunk"
                )
            )
        );
    }

    public function testPOSTRepositoryForProjectMember()
    {
        $params = json_encode(
            array(
                "project_id" => $this->svn_project_id,
                "name"       => "my_repository_03",
            )
        );

        $this->setExpectedException('Guzzle\Http\Exception\ClientErrorResponseException');
        $response = $this->getResponseWithProjectMember($this->client->post('svn', null, $params));
        $this->assertEquals($response->getStatusCode(), 401);
    }

    public function testPOSTWithALayout()
    {
        $params = json_encode(
            array(
                'project_id' => $this->svn_project_id,
                'name'       => 'my_repository_04',
                'settings'   => array(
                    'layout' => array('/trunk', '/branches')
                )
            )
        );

        $response = $this->getResponse($this->client->post('svn', null, $params));
        $this->assertEquals($response->getStatusCode(), 201);
    }

    public function testPUTRepository()
    {
        $data = json_encode(
            array(
                'settings' => array(
                    'commit_rules'        => array(
                        'is_reference_mandatory'           => true,
                        'is_commit_message_change_allowed' => false
                    ),
                    "access_file"         => "[/]\r\n* = rw\r\n@members = rw",
                    "immutable_tags"      => array(
                        "paths"     => array(),
                        "whitelist" => array()
                    ),
                    "email_notifications" => array(
                        array(
                            'path'   => "/tags",
                            'emails' => array(
                                "project-announce@list.example.com",
                                "project-devel@lists.example.com"
                            )
                        )
                    )
                )
            )
        );

        $response = $this->getResponse($this->client->put('svn/1', null, $data));

        $repository = $response->json();

        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($repository['name'], 'repo01');
        $this->assertEquals(
            $repository['settings']['commit_rules'],
            array(
                "is_reference_mandatory"           => true,
                "is_commit_message_change_allowed" => false
            )
        );
        $this->assertEquals(
            $repository['settings']['immutable_tags'],
            array(
                "paths"     => array(),
                "whitelist" => array(),
            )
        );
        $this->assertEquals(
            $repository['settings']['access_file'],
            "[/]\r\n* = rw\r\n@members = rw"
        );

        $this->assertEquals(
            $repository['settings']['email_notifications'],
            array(
                array(
                    'path'        => "/tags",
                    'emails'      => array(
                        "project-announce@list.example.com",
                        "project-devel@lists.example.com"
                    ),
                    "user_groups" => array(),
                    "users"       => array()
                )
            )
        );
    }

    public function testOPTIONS()
    {
        $response = $this->getResponse($this->client->get('svn/1'));

        $this->assertEquals(array('OPTIONS', 'GET', 'PUT', 'DELETE'), $response->getHeader('Allow')->normalize()->toArray());
    }

    public function testOPTIONSId()
    {
        $response = $this->getResponseByToken(
            $this->getTokenForUserName(REST_TestDataBuilder::TEST_USER_1_NAME),
            $this->client->options('svn')
        );

        $this->assertEquals(array('OPTIONS', 'POST'), $response->getHeader('Allow')->normalize()->toArray());
    }
}
