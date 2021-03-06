<?php

Route::group(['prefix' => 'admin'], function () {
    Route::get('login', 'AdminController@login');
    Route::get('logout', 'Auth\LoginController@logout');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('captcha', 'Auth\LoginController@captcha');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth.admin'], function () {

    Route::get('/', 'AdminController@dashboard');
    Route::get('/index', 'AdminController@dashboard');
    Route::get('dashboard', 'AdminController@dashboard');
    Route::get('hours', 'AdminController@hours');
    Route::get('areas', 'AdminController@areas');
    Route::get('browsers', 'AdminController@browsers');

    /**
     * 推送管理
     */
    Route::get('push/logs', 'PushController@log');
    Route::get('push/logs/table', 'PushController@logTable');
    Route::get('push/send', 'PushController@send');

    /**
     * 短信管理
     */
    Route::get('sms/logs', 'SmsController@log');
    Route::get('sms/logs/table', 'SmsController@logTable');

    /**
     * 消息管理
     */
    Route::get('messages/table', 'MessageController@table');
    Route::post('messages/state/{state}', 'MessageController@state');
    Route::resource('messages', 'MessageController');
    Route::get('messages/pass/{id}', 'MessageController@pass');
    Route::get('messages/{id}/delete', 'MessageController@destroy');

    /**
     * 文件管理
     */
    Route::post('files/upload', 'FileController@upload');
    Route::post('files/delete', 'FileController@delete');

    /**
     * 个人信息
     */
    Route::resource('profiles', 'ProfileController');

    /**
     * 后台用户管理
     */
    Route::post('users/category/{id}', 'UserController@category');
    Route::get('users/tree/{id}', 'UserController@tree');
    Route::post('users/grant/{id}', 'UserController@grant');
    Route::get('users/table', 'UserController@table');
    Route::get('users/logs', 'UserController@log');
    Route::get('users/logs/table', 'UserController@logTable');
    Route::get('users/{id}/delete', 'UserController@destroy');
    Route::resource('users', 'UserController');

    /**
     * 会员管理
     */
    Route::get('members/table', 'MemberController@table');
    Route::get('members/state/{id}', 'MemberController@state');
    Route::get('members/sort', 'MemberController@sort');
    Route::get('members/comments/{id}','MemberController@comments');
    Route::get('members/wallet/{id}','MemberController@wallet');
    Route::post('members/{id}/top', 'MemberController@top');
    Route::post('members/{id}/tag', 'MemberController@tag');
    Route::resource('members', 'MemberController');

    /**
     * 角色管理
     */
    Route::get('roles/table', 'RoleController@table');
    Route::resource('roles', 'RoleController');
    Route::get('roles/{id}/delete', 'RoleController@destroy');

    /**
     * 参数设置
     */
    Route::get('options/table', 'OptionController@table');
    Route::get('options/{id}/save', 'OptionController@save');
    Route::resource('options', 'OptionController');

    /**
     * 数据字典
     */
    Route::get('dictionaries/tree/', 'DictionaryController@tree');
    Route::get('dictionaries/table/{parent_id}', 'DictionaryController@table');
    Route::get('dictionaries/create/{parent_id}', 'DictionaryController@create');
    Route::get('dictionaries/{id}/save', 'DictionaryController@save');
    Route::resource('dictionaries', 'DictionaryController');
    Route::get('dictionaries/{id}/delete', 'DictionaryController@destroy');

    /**
     * 应用管理
     */
    Route::get('apps/table', 'AppController@table');
    Route::resource('apps', 'AppController');
    Route::get('apps/{id}/delete', 'AppController@destroy');

    /**
     * 站点管理
     */
    Route::get('sites/table', 'SiteController@table');
    Route::get('sites/{id}/publish', 'SiteController@publish');
    Route::resource('sites', 'SiteController');

    /**
     * 模块管理
     */
    Route::get('modules/table', 'ModuleController@table');
    Route::get('modules/{id}/save', 'ModuleController@save');
    Route::get('modules/{id}/migrate', 'ModuleController@migrate');
    Route::get('modules/{id}/generate', 'ModuleController@generate');
    Route::post('modules/copy', 'ModuleController@copy');
    Route::resource('modules', 'ModuleController');

    /**
     * 字段管理
     */
    Route::get('modules/fields/{module_id}/table', 'ModuleFieldController@table');
    Route::post('modules/fields/{module_id}/save', 'ModuleFieldController@save');
    Route::resource('modules/fields', 'ModuleFieldController');

    /**
     * 菜单管理
     */
    Route::get('menus/modules', 'MenuController@modules');
    Route::post('menus/sort', 'MenuController@sort');
    Route::resource('menus', 'MenuController');

    /**
     * 主题管理
     */
    Route::get('themes/tree', 'ThemeController@tree');
    Route::get('themes/file', 'ThemeController@readFile');
    Route::post('themes/file', 'ThemeController@createFile');
    Route::put('themes/file', 'ThemeController@writeFile');
    Route::delete('themes/file', 'ThemeController@removeFile');
    Route::get('themes/modules/{module_id}', 'ThemeController@module');
    Route::resource('themes', 'ThemeController');

    /**
     * 栏目管理
     */
    Route::get('categories/tree/', 'CategoryController@tree');
    Route::get('categories/table/{category_id}', 'CategoryController@table');
    Route::get('categories/create/{category_id}', 'CategoryController@create');
    Route::get('categories/{id}/save', 'CategoryController@save');
    Route::resource('categories', 'CategoryController');
    Route::get('categories/{id}/delete', 'CategoryController@destroy');

    /**
     * 问答管理
     */
    Route::post('questions/reply/{id}', 'QuestionController@reply');

    /**
     * 评论管理
     */
    Route::get('comments/table', 'CommentController@table');
    Route::get('comments/replies/{id}', 'CommentController@replies');
    Route::post('comments/{id}/reply', 'CommentController@reply');
    Route::post('comments/state', 'CommentController@state');
    Route::resource('comments', 'CommentController');
    Route::get('comments/pass/{id}', 'CommentController@pass');
    Route::get('comments/{id}/delete', 'CommentController@destroy');

    /**
     * 标签管理
     */

    Route::get('tags/table', 'TagController@table');
    Route::get('tags/tree', 'TagController@tree');
    Route::get('tags/sort', 'TagController@sort');
    Route::resource('tags', 'TagController');

    /**
     * 问卷管理
     */
    Route::get('surveys/items/table/{survey_id}', 'SurveyItemController@table');
    Route::resource('surveys/items', 'SurveyItemController');

    Route::get('surveys/table', 'SurveyController@table');
    Route::post('surveys/{id}/top', 'SurveyController@top');
    Route::post('surveys/{id}/tag', 'SurveyController@tag');
    Route::get('surveys/statistic/{survey_id}', 'SurveyController@statistic');
    Route::post('surveys/state', 'SurveyController@state');
    Route::get('surveys/sort', 'SurveyController@sort');
    Route::get('surveys/comments/{id}','SurveyController@comments');
    Route::resource('surveys', 'SurveyController');

    /**
     * 投票管理
     */
    Route::get('votes/items/table/{vote_id}', 'VoteItemController@table');
    Route::resource('votes/items', 'VoteItemController');
    Route::get('votes/sort', 'VoteController@sort');
    Route::get('votes/table', 'VoteController@table');
    Route::get('votes/statistic/{vote_id}', 'VoteController@statistic');
    Route::post('votes/state', 'VoteController@state');
    Route::post('votes/{id}/top', 'VoteController@top');
    Route::post('votes/{id}/tag', 'VoteController@tag');
    Route::get('votes/comments/{id}','VoteController@comments');
    Route::resource('votes', 'VoteController');
});