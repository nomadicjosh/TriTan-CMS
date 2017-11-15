<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

// RESTful API
$app->group('/rest', function() use ($app, $orm) {

    /**
     * Will result in /rest/.
     */
    $app->get('/', function () use($app) {
        $app->res->_format('json', 404);
    });

    /**
     * Will result in /rest/v1/.
     */
    $app->get('/v1/', function () use($app) {
        $app->res->_format('json', 404);
    });

    /**
     * @apiDescription Retrieve all records from a requested document.
     * @api {get} /rest/v1/:api_key/:document/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/user/
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "_id": "599b7d84e549d",
     *          "user_id": 1,
     *          "user_login": "tritan",
     *          "user_fname": "TriTan",
     *          "user_lname": "CMS",
     *          "user_email": "tritan@tritan.com",
     *          "user_status": "A",
     *          "user_role": 1,
     *          "user_registered": "2017-08-20 12:30:20",
     *          "user_modified": "2017-08-29 23:55:30"
     *      }
     */
    $app->get('/v1/(\w+)/(\w+)', function ($key, $table) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $sql = $app->db->table($table)->all();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });

    /**
     * @apiDescription Retrieve all records from a requested document sorted by a particular field.
     * @api {get} /rest/v1/:api_key/:document/sort/:field/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/user/sort/user_login/
     */
    $app->get('/v1/(\w+)/(\w+)/sort/(\w+)/', function ($key, $table, $sort) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $sql = $app->db->table($table)
            ->sortBy($sort)
            ->get();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });

    /**
     * @apiDescription Retrieve all records from a requested document sorted by a particular field in descending order.
     * @api {get} /rest/v1/:api_key/:document/sort/:field/desc/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/user/sort/user_login/desc/
     */
    $app->get('/v1/(\w+)/(\w+)/sort/(\w+)/desc/', function ($key, $table, $sort) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $sql = $app->db->table($table)
            ->sortBy($sort, 'desc')
            ->get();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });

    /**
     * @apiDescription Retrieve all records from a requested document sorted by a particular field in descending order.
     * @api {get} /rest/v1/:api_key/:document1/many/:document2/:field2/:field1/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/user/many/ttcms_1_post/post_author/user_id/
     */
    $app->get('/v1/(\w+)/(\w+)/many/(\w+)/(\w+)/(\w+)/', function ($key, $table1, $table2, $field1, $field2) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $firstCollection = $app->db->table($table1);
        $secondCollection = $app->db->table($table2);
        $sql = $firstCollection->withMany($secondCollection, "{$table2}s", $field1, '=', $field2)->get();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });

    /**
     * @apiDescription Retrieve all records from a requested document sorted by a particular field in descending order.
     * @api {get} /rest/v1/:api_key/:document/sort/:field/desc/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/ttcms_1_post/one/user/user_id/post_author/
     */
    $app->get('/v1/(\w+)/(\w+)/one/(\w+)/(\w+)/(\w+)/', function ($key, $table1, $table2, $field1, $field2) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $firstCollection = $app->db->table($table1);
        $secondCollection = $app->db->table($table2);
        $sql = $firstCollection->withMany($secondCollection, "{$table2}s", $field1, '=', $field2)->get();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });

    /**
     * @apiDescription Retrieve a record based on field|value from a requested document.
     * @api {get} /rest/v1/:api_key/:document/:field/:value/
     * @apiVersion 1.0.0
     * 
     * @apiGroup REST API
     * 
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8888/rest/v1/UJHUPtxpEgezl45gjazX/user/user_login/tritan/
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "_id": "599b7d84e549d",
     *          "user_id": 1,
     *          "user_login": "tritan",
     *          "user_fname": "TriTan",
     *          "user_lname": "CMS",
     *          "user_email": "tritan@tritan.com",
     *          "user_status": "A",
     *          "user_role": 1,
     *          "user_registered": "2017-08-20 12:30:20",
     *          "user_modified": "2017-08-29 23:55:30"
     *      }
     */
    $app->get('/v1/(\w+)/(\w+)/(\w+)/(.+)', function ($key, $table, $field, $any) use($app) {
        if ($key !== $app->hook->{'get_option'}('api_key') || $app->hook->{'get_option'}('api_key') === null) {
            $app->res->_format('json', 401);
            exit();
        }
        $sql = $app->db->table($table)->where($field, $any)->get();
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql === false) {
            $app->res->_format('json', 404);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then a 200
         * status should be sent. Why? Check out
         * the accepted answer at
         * http://stackoverflow.com/questions/13366730/proper-rest-response-for-empty-table/13367198#13367198
         */ elseif (empty($sql) === true) {
            $app->res->_format('json', 200);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a json format.
         */ else {
            $app->res->_format('json', 200, $sql);
        }
    });
});
