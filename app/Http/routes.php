<?php

ini_set('xdebug.var_display_max_depth', 10);
set_time_limit(0);

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/*$app->get('/', function () {
    return View('login');
});*/

/**
 * Getters for Users
 */

$app->get('/login', 'UserController@login');
$app->get('/getUser', 'UserController@getUser');
$app->get('/getShopInventory', 'UserController@getShopInventory');
$app->get('/getMuseumAssets', 'UserController@getMuseumAssets');
$app->get('/getBooks', 'UserController@getBooks');
$app->get('/getGamesStatistics', 'UserController@gamesStatistics');
$app->get('/getMuseumCustomNote', 'UserController@getMuseumCustomNote');
$app->get('/getCinemaData', 'UserController@getCinema');
$app->get('/getTvShowQuestions', 'UserController@getTvShowQuestions');

/**
 * Setters for users
 */

$app->get('/setCoinNumber', 'UserController@setCoinNumber');
$app->get('/setMinigameData', 'UserController@setMinigameData');
$app->get('/setMusicHallStars', 'UserController@setMusicHallStars');
$app->get('/setCharacter', 'UserController@setCharacter');
$app->get('/setCinema', 'UserController@setCinema');
$app->get('/setMinigamePlaytime', 'UserController@setMinigamePlaytime');
$app->get('/setMinigameDifficulty', 'UserController@setMinigameDifficulty');
$app->get('/setInstrumentPurchased', 'UserController@setInstrumentPurchased');

/**
 * Getters for Teachers
 */

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'teachers'], function() use ($app){
    $app->get('/getClassList', 'TeacherController@getClassList');
    $app->get('/getUserList', 'TeacherController@getUserList');
    $app->get('/getUserScores', 'TeacherController@getUserScores');
    $app->get('/getUserLevels', 'TeacherController@getUserLevels');
    $app->get('/getUserTodayPlaytime', 'TeacherController@getUserTodayPlaytime');
    $app->get('/getUserTotalPlaytime', 'TeacherController@getUserTotalPlaytime');
    $app->get('/getClassScoreIndividual', 'TeacherController@getClassScoreIndividual');
    $app->get('/getClassScoreTotal', 'TeacherController@getClassScoreTotal');
    $app->get('/getClassInstrumentAccess', 'TeacherController@getClassInstrumentAccess');
    $app->get('/getClassBookAccess', 'TeacherController@getClassBookAccess');
    $app->get('/getClassMinigameAccess', 'TeacherController@getClassMinigameAccess');
    $app->get('/getStudentInstrumentAccess', 'TeacherController@getStudentInstrumentAccess');
    $app->get('/getStudentBookAccess', 'TeacherController@getStudentBookAccess');
    $app->get('/getStudentMinigameAccess', 'TeacherController@getStudentMinigameAccess');
    $app->get('/getTeacherMuseumCustomBooks', 'TeacherController@getTeacherMuseumCustomBooks');
    $app->get('/getTeacherMuseumCustomNote', 'TeacherController@getTeacherMuseumCustomNote');
    $app->get('/getTvShowData', 'TeacherController@getTvShowData');
});

/**
 * Setters for Teachers
 */

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'teachers'], function() use ($app){
    $app->get('setClassId', 'TeacherController@setClassId');
    $app->get('setStudentMinigameRestriction', 'TeacherController@setStudentMinigameRestriction');
    $app->get('setStudentInstrumentRestriction', 'TeacherController@setStudentInstrumentRestriction');
    $app->get('setStudentBookRestriction', 'TeacherController@setStudentBookRestriction');
    $app->get('setStudentAllowedAllInstruments', 'TeacherController@setStudentAllowedAllInstruments');
    $app->get('setClassMinigameRestriction', 'TeacherController@setClassMinigameRestriction');
    $app->get('setClassInstrumentRestriction', 'TeacherController@setClassInstrumentRestriction');
    $app->get('setClassBookRestriction', 'TeacherController@setClassBookRestriction');
    $app->get('setClassAllowedAllInstruments', 'TeacherController@setClassAllowedAllInstruments');
    $app->get('setTVShowSettings', 'TeacherController@setTVShowSettings');
    $app->get('setBook', 'TeacherController@setBook');
    $app->get('deleteBook', 'TeacherController@deleteBook');
    $app->get('setClassCustomQuestions', 'TeacherController@setClassCustomQuestions');
    $app->get('deleteClassCustomQuestions', 'TeacherController@deleteClassCustomQuestions');
    $app->get('setCustomNote', 'TeacherController@setCustomNote');

    // TO DO
    /*
     * update Custom Book
     * update Custom Question
     * 9 ore + 10,5 ore
     */
});

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'parse'], function() use ($app){

    $app->get('/db', 'ParseController@populateDatabase');

    //$app->get('/updateUserInstruments', 'ParseController@urgentlyUpdateUserInstruments');

    $app->get('/updateUsers', 'ParseController@updateUsers');

    /*$app->get('/tables', 'ParseController@populateTables');
    $app->get('/classes', 'ParseController@classes');
    $app->get('/users', 'ParseController@users');
    $app->get('/addUsersWithoutUnilogin', 'ParseController@addUsersWithoutUnilogin');

    $app->get('/updateClassGamesAccess', 'ParseController@updateClassGamesAccess');
    $app->get('/updateUserGamesAccess', 'ParseController@updateUserGamesAccess');
    $app->get('/updateClassBooksAccess', 'ParseController@updateClassBooksAccess');
    $app->get('/updateUserInstrumentsAccess', 'ParseController@updateUserInstrumentsAccess');*/
});