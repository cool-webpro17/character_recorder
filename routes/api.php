<?php


Route::group([
        'prefix' => '/v1',
        'namespace' => 'Api\V1',
        'middleware' => ['cors'],
        'as' => 'api.'
    ], function () {
    Route::post('log',                                  ['as' => 'log',                         'uses' => 'HomeController@log']);
    Route::get('activity_log',                          ['as' => 'activity_log',                'uses' => 'HomeController@activity_log']);
    Route::get('standard_characters',                   ['as' => 'standard_characters',         'uses' => 'HomeController@getStandardCharacters']);
    Route::get('user-tag/{userId}',                     ['as' => 'get_user_tag',                'uses' => 'HomeController@getUserTags']);
    Route::post('user-tag/create',                      ['as' => 'create_user_tag',             'uses' => 'HomeController@createUserTag']);
    Route::post('user-tag/remove',                      ['as' => 'remove_user_tag',             'uses' => 'HomeController@removeUserTag']);
    Route::post('matrix-store',                         ['as' => 'store_matrix',                'uses' => 'HomeController@storeMatrix']);
    Route::post('delete-header/{headerId}',             ['as' => 'delete_header',               'uses' => 'HomeController@deleteHeader']);
    Route::post('change-taxon/{taxon}',                 ['as' => 'change_taxon',                'uses' => 'HomeController@changeTaxon']);
    Route::post('add-more-column/{columnCount}',        ['as' => 'add_more_column',             'uses' => 'HomeController@addMoreColumn']);
    Route::post('show-tab-character/{tabName}',         ['as' => 'show_tab_character',          'uses' => 'HomeController@showTabCharacter']);
    Route::post('export-description',                   ['as' => 'export_description',          'uses' => 'HomeController@exportDescription']);

    Route::group([
        'prefix' => '/character',
        'as' => 'character.'
    ], function () {
        Route::post('create',                           ['as' => 'create_character',            'uses' => 'HomeController@storeCharacter']);
        Route::post('add-character',                    ['as' => 'add_character',               'uses' => 'HomeController@addCharacter']);
        Route::get('{userId}',                          ['as' => 'get_character',               'uses' => 'HomeController@getCharacter']);
        Route::get('all',                               ['as' => 'get_all_character',           'uses' => 'HomeController@getAllCharacter']);
        Route::post('update',                           ['as' => 'update_character',            'uses' => 'HomeController@updateCharacter']);
        Route::post('update-unit',                      ['as' => 'update_unit',                 'uses' => 'HomeController@updateUnit']);
        Route::post('update-summary',                   ['as' => 'update_summary',              'uses' => 'HomeController@updateSummary']);
        Route::post('delete/{userId}/{characterId}',    ['as' => 'delete_character',            'uses' => 'HomeController@deleteCharacter']);
        Route::post('add-standard',                     ['as' => 'add_standard_character',      'uses' => 'HomeController@addStandardCharacter']);
        Route::post('remove-all-standard',              ['as' => 'remove_all_standard',         'uses' => 'HomeController@removeAllStandard']);
        Route::post('remove-all',                       ['as' => 'remove_all',                  'uses' => 'HomeController@removeAll']);


        Route::get('usage/{characterId}',               ['as' => 'usage',                       'uses' => 'HomeController@usage']);
        Route::post('delete-header/{headerId}',         ['as' => 'delete-header',               'uses' => 'HomeController@deleteHeader']);
    });
});
