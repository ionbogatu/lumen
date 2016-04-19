<?php

namespace App\Http\Controllers;

set_time_limit(12000);

use Illuminate\Support\Facades\DB;
use App\ParseData;

class ParseController extends Controller
{

    private $basepath = "C:\\Users\\Otinsoft\\Desktop\\WebGame DB\\ParseData\\";

    private function generateUniqueKeyValue($table){
        $alphabet = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G' ,'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8',
            '9'];

        $ids = DB::table($table)->lists('id');

        $key = null;

        do{
            $key = "";
            for($i = 0; $i < 10; $i++){
                $key .= $alphabet[mt_rand(0, 61)];
            }
        }while(in_array($key, $ids));

        return $key;
    }

    private function classResolveDependencies($classId){

        $books = DB::table('books')
            ->get();

        $games = DB::table('games')
            ->get();

        $instruments = DB::table('shop_inventory')
            ->get();

        foreach($books as $book){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_books_access'),
                'class_id' => $classId,
                'book_id' => $book->id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('class_books_access')
            ->insert($preparedRows)
        ){
            die("Cannot populate class_books_access table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        $preparedRows = array();

        foreach($games as $game){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_games_access'),
                'class_id' => $classId,
                'game_id' => $game->game_id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('class_games_access')
            ->insert($preparedRows)
        ){
            die("Cannot populate class_games_access table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        $preparedRows = array();

        foreach($instruments as $instrument){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_instruments_access'),
                'class_id' => $classId,
                'instrument_id' => $instrument->id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('class_instruments_access')
            ->insert($preparedRows)
        ){
            die("Cannot populate class_instruments_access table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

    }

    public function classes(){

        $classes = file_get_contents($this->basepath . 'MSClass.json');
        $classes = json_decode($classes)->results;

        $preparedRows = array();

        foreach($classes as $class){
            if(
                !isset($class->objectId) ||
                !isset($class->title) ||
                !isset($class->groupId) ||
                !isset($class->msschool->objectId) ||
                !isset($class)
            ){
                continue;
            }

            $teacherId = null;

            $teacherId = DB::table('users')
                ->where('role_id', 'KWXgk5xtKg')
                ->where('class_id', $class->objectId)
                ->value('id');


            $preparedRows[] = array(
                'id' => $class->objectId,
                'name' => $class->title,
                'group' => $class->groupId,
                'use_default_questions' => true,
                'use_custom_questions' => false,
                'teacher_id' => $teacherId,
                'school_id' => $class->msschool->objectId,
                'museum_custom_note_id' => '3Pibw9HH',
                'is_default' => false
            );

            $this->classResolveDependencies($class->objectId);

        }

        if(
        !DB::table('classes')
            ->insert($preparedRows)
        ){
            die("Cannot populate classes table");
        }else{
            //echo "success</br>";
        }

    }

    public function getAssetURL($assetId){
        $museum_sub_assets = file_get_contents($this->basepath . "Asset.json");
        $museum_sub_assets = json_decode($museum_sub_assets)->results;

        foreach($museum_sub_assets as $museum_sub_asset){
            if($museum_sub_asset->objectId == $assetId){
                $path = $museum_sub_asset->file->url;
                $path = explode('/', $path);
                $path = $path[count($path) - 1];
                $path = 'assets/' . $path;
                return $path;
            }
        }
    }

    public function parseMuseumSubAssets(){
        $museum_sub_assets = file_get_contents($this->basepath . "MuseumSubAsset.json");
        $museum_sub_assets = json_decode($museum_sub_assets)->results;

        foreach($museum_sub_assets as $asset){
            $title = isset($asset->title) ? $asset->title : "";
            $titleEn = isset($asset->titleEn) ? $asset->titleEn : "";
            $subtitle = isset($asset->subtitle) ? $asset->subtitle : "";
            $subtitleEn = isset($asset->subtitleEn) ? $asset->subtitleEn : "";
            $text = isset($asset->textDescription) ? $asset->textDescription : "";
            $textEn = isset($asset->textDescriptionEn) ? $asset->textDescriptionEn : "";
            $destinatar_id = isset($asset->museumAsset->objectId) ? $asset->museumAsset->objectId : "";
            $box_number = isset($asset->boxNumber) ? $asset->boxNumber : "";
            $sound1_name = isset($asset->assetSound1Title) ? $asset->assetSound1Title : "";
            $sound1_nameEn = isset($asset->assetSound1TitleEn) ? $asset->assetSound1TitleEn : "";
            $sound2_name = isset($asset->assetSound2Title) ? $asset->assetSound2Title : "";
            $sound2_nameEn = isset($asset->assetSound2TitleEn) ? $asset->assetSound2TitleEn : "";
            $sound1_url = isset($asset->assetSound1->objectId) ? $asset->assetSound1->objectId : "";
            $sound2_url = isset($asset->assetSound2->objectId) ? $asset->assetSound2->objectId : "";
            $image = isset($asset->assetImage->objectId) ? $asset->assetImage->objectId : "";

            if(
                isset($title) &&
                isset($subtitle) &&
                isset($text) &&
                isset($destinatar_id) &&
                isset($box_number)
            ){
                $preparedRows[] = array(
                    'id' => $this->generateUniqueKeyValue('museum_sub_assets'),
                    'title' => utf8_encode($title),
                    'subtitle' => utf8_encode($subtitle),
                    'text' => utf8_encode($text),
                    'language_id' => 2,
                    'destinatar_id' => $destinatar_id,
                    'box_number' => $box_number,
                    'sound1_name' => utf8_encode($sound1_name),
                    'sound2_name' => utf8_encode($sound2_name),
                    'sound1_url' => $this->getAssetURL($sound1_url),
                    'sound2_url' => $this->getAssetURL($sound2_url),
                    'image' => $this->getAssetURL($image)
                );
            }

            if(
                isset($titleEn) &&
                isset($subtitleEn) &&
                isset($textEn) &&
                isset($destinatar_id) &&
                isset($box_number)
            ){
                $preparedRows[] = array(
                    'id' => $this->generateUniqueKeyValue('museum_sub_assets'),
                    'title' => $titleEn,
                    'subtitle' => $subtitleEn,
                    'text' => $textEn,
                    'language_id' => 1,
                    'destinatar_id' => $destinatar_id,
                    'box_number' => $box_number,
                    'sound1_name' => $sound1_nameEn,
                    'sound2_name' => $sound2_nameEn,
                    'sound1_url' => $this->getAssetURL($sound1_url),
                    'sound2_url' => $this->getAssetURL($sound2_url),
                    'image' => $this->getAssetURL($image)
                );
            }
        }

        if(
        !DB::table('museum_sub_assets')
            ->insert($preparedRows)
        ){
            die("Cannot populate museum_sub_assets");
        }else{
            //echo "success";
        }
    }

    public function parseMuseumDefaultNotes(){
        $museum_notes = file_get_contents($this->basepath . "MuseumCustomNote.json");
        $museum_notes = json_decode($museum_notes)->results;

        $preparedRows = array();

        foreach($museum_notes as $museum_note){
            if(
                !isset($museum_note->isStandard) ||
                !isset($museum_note->textDescription) ||
                !isset($museum_note->textDescriptionEn) ||
                !isset($museum_note->title) ||
                !isset($museum_note->titleEn) ||
                !isset($museum_note->audioFile->name) ||
                !isset($museum_note->audioFile->url) ||
                !isset($museum_note->audioFileEn->name) ||
                !isset($museum_note->audioFileEn->url)

            ){
                continue;
            }

            if($museum_note === false){
                continue;
            }

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('museum_default_notes'),
                'title' => $museum_note->title,
                'text_description' => $museum_note->textDescription,
                'language_id' => 2,
                'audiofile_name' => $museum_note->audioFile->name,
                'audiofile_url' => $museum_note->audioFile->url,
            );

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('museum_default_notes'),
                'title' => $museum_note->titleEn,
                'text_description' => $museum_note->textDescriptionEn,
                'language_id' => 2,
                'audiofile_name' => $museum_note->audioFileEn->name,
                'audiofile_url' => $museum_note->audioFileEn->url,
            );
        }

        if(
            !DB::table('museum_default_notes')
                ->insert($preparedRows)
        ){
            die("Cannot pupulate museum_default_notes");
        }
    }

    public function parseMuseumAssets(){

        $this->parseMuseumDefaultNotes();

        $museum_assets = file_get_contents($this->basepath . "MuseumAsset.json");
        $museum_assets = json_decode($museum_assets)->results;

        $preparedRows = array();

        foreach($museum_assets as $museum_asset){
            if(
                !isset($museum_asset->objectId) ||
                !isset($museum_asset->name) ||
                !isset($museum_asset->placement)
            ){
                continue;
            }

            $preparedRows[] = array(
                'id' => $museum_asset->objectId,
                'name' => $museum_asset->name,
                'placement' => $museum_asset->placement,
            );
        }

        if(
            !DB::table('museum_assets')
                ->insert($preparedRows)
        ){
            die("Cannot populate museum_assets table");
        }

        $this->parseMuseumSubAssets();
    }

    public function loadDefaultQuestions(){
        $questions = file_get_contents($this->basepath . "Questions.json");
        $questions = json_decode($questions)->results;

        $preparedRows = array();

        foreach($questions as $question){
            if(
                !isset($question->objectId) ||
                !isset($question->level) ||
                !isset($question->title) ||
                !isset($question->lang) ||
                !isset($question->type) ||
                !isset($question->additionalText) ||
                !isset($question->additionalAsset->objectId) ||
                !isset($question->correctAnswerString) ||
                !isset($question->correctAnswerAsset->objectId) ||
                !isset($question->wrongAnswerString1) ||
                !isset($question->wrongAnswerAsset1->objectId) ||
                !isset($question->wrongAnswerString2) ||
                !isset($question->wrongAnswerAsset2->objectId) ||
                !isset($question->wrongAnswerString3) ||
                !isset($question->wrongAnswerAsset4->objectId)
            ){
                continue;
            }

            $preparedRows[] = array(
                'id' => $question->objectId,
                'difficultyLevel' => $question->level,
                'title' => $question->title,
                'question' => $question->additionalText,
                'question_asset' => $this->getAssetURL($question->additioanlAsset->objectId),
                'correctAnswer' => $question->correctAnswerString,
                'correctAnswerAsset' => $this->getAssetURL($question->coorectAnswerAsset->objectId),
                'wrongAnswer1' => $question->wrongAnswerString1,
                'wrongAnswer1Asset' => $this->getAssetURL($question->wrongAnswerAsset1->objectId),
                'wrongAnswer2' => $question->wrongAnswerString2,
                'wrongAnswer2Asset' => $this->getAssetURL($question->wrongAnswerAsset2->objectId),
                'wrongAnswer3' => $question->wrongAnswerString3,
                'wrongAnswer4Asset' => $this->getAssetURL($question->wrongAnswerAsset3->objectId),
                'language_id' => $question->lang,
                'type' => $question->type
            );
        }

        if(
            !DB::table('questions')
                ->insert($preparedRows)
        ){
            die("Cannot populate questions table");
        }

    }

    public function loadBasicTables(){
        // assets

        if(
            !DB::table('asset_type')
                ->insert([
                    [
                        'id' => 1,
                        'type' => 'asset'
                    ],
                    [
                        'id' => 2,
                        'type' => 'sound'
                    ],[
                        'id' => 3,
                        'type' => 'image'
                    ]
                ])
        ){
            die("cannot populate asset_type table");
        }

        // character

        if(
            !DB::table('character')
                ->insert([
                    [
                        'characterId' => 'ihnxqTvlMp',
                        'character' => 'Funky'
                    ],
                    [
                        'characterId' => 'S0xrM3z67k',
                        'character' => 'Philia'
                    ]
                ])
        ){
            die("Cannot populate character table");
        }

        // languages

        if(
            !DB::table("languages")
                ->insert([
                    [
                        'id' => 1,
                        'language' => 'en'
                    ],
                    [
                        'id' => 2,
                        'language' => 'da-DK'
                    ]
                ])
        ){
            die("Cannot populate languages table");
        }

        // role

        if(
        !DB::table("role")
            ->insert([
                [
                    'id' => '7PG6XCRphk',
                    'role' => 'Pupil'
                ],
                [
                    'id' => 'KWXgk5xtKg',
                    'role' => 'Teacher'
                ]
            ])
        ){
            die("Cannot populate languages table");
        }

        // questions

        $this->loadDefaultQuestions();
    }

    private function getBookIsAllowed($bookId, $classId){
        $book_restrictions = file_get_contents($this->basepath . 'BookRestriction.json');
        $book_restrictions = json_decode($book_restrictions)->results;

        foreach($book_restrictions as $book_restriction){
            if(
                !isset($book_restriction->objectId) ||
                !isset($book_restriction->msclass->objectId)
            ){
                continue;
            }

            if(
                ($bookId == $book_restriction->objectId) &&
                ($classId == $book_restriction->msclass->objectId)
            ){
                return array(
                    'isAllowed' => false,
                    'allowed_startDatetime' => time(),
                    'allowed_endDatetime' => strtotime($book_restriction->expireDatetime->iso)
                );
            }
        }

        return array(
            'isAllowed' => true,
            'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
            'allowed_endDatetime' => date("Y-m-d H:i:s", 0)
        );
    }

    public function loadBasicDependencies(){

        $this->parseMuseumAssets();

        $books = file_get_contents($this->basepath . 'Book.json');
        $books = json_decode($books)->results;

        $games = file_get_contents($this->basepath . 'MSGame.json');
        $games = json_decode($games)->results;

        $instruments = file_get_contents($this->basepath . 'Instrument.json');
        $instruments = json_decode($instruments)->results;

        $preparedRows = null;
        $preparedRows = array();

        foreach($books as $book){

            if(
                !isset($book->msclass->objectId) ||
                !isset($book->msuser->objectId) ||
                !isset($book->number) ||
                !isset($book->objectId)
            ){
                continue;
            }

            $bookAllowed = $this->getBookIsAllowed($book->objectd, $book->msclass->objectId);

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('books'),
                'teacher_id' => $book->msuser->objectId,
                'box_number' => $book->number,
                'book_title' => isset($book->title) ? $book->title : '',
                'language_id' => 2,
                'title1' => $book->title1,
                'title2' => $book->title2,
                'title3' => $book->title3,
                'title4' => $book->title4,
                'title5' => $book->title5,
                'title6' => $book->title6,
                'desc1' => $book->desc1,
                'desc2' => $book->desc2,
                'desc3' => $book->desc3,
                'desc4' => $book->desc4,
                'desc5' => $book->desc5,
                'desc6' => $book->desc6,
                'class_id' => $book->msclass->object_id,
                'is_allowed' => $bookAllowed['isAllowed'],
                'allowed_startDatetime' => $bookAllowed['allowed_startDatetime'],
                'allowed_endDatetime' => $bookAllowed['allowed_endDatetime'],
            );

        }

        if(
        !DB::table('books')
            ->insert($preparedRows)
        ){
            die("Cannot populate books table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        $preparedRows = null;
        $preparedRows = array();

        foreach($games as $game){

            if(
                !isset($game->objectId) ||
                !isset($game->title)
            ){
                continue;
            }

            $preparedRows[] = array(
                'game_id' => $game->objectId,
                'title' => $game->title
            );

        }

        if(
        !DB::table('games')
            ->insert($preparedRows)
        ){
            die("Cannot populate games table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        $preparedRows = null;
        $preparedRows = array();

        foreach($instruments as $instrument){
            if(
                !isset($instrument->objectId) ||
                !isset($instrument->title) ||
                !isset($instrument->titleEn) ||
                !isset($instrument->textDescription) ||
                !isset($instrument->textDescriptionEn)
            ){
                continue;
            }

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('user_instruments'),
                'instrument_id' => $instrument->objectId,
                'title' => $instrument->titleEn,
                'text' => $instrument->textDescriptionEn,
                'language_id' => 1,
                'price' => $instrument->price
            );

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('user_instruments'),
                'instrument_id' => $instrument->objectId,
                'title' => $instrument->title,
                'text' => $instrument->textDescription,
                'language_id' => 2,
                'price' => $instrument->price
            );

        }

        if(
        !DB::table('shop_inventory')
            ->insert($preparedRows)
        ){
            die("Cannot populate shop_inventory table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

    }

    private function getLevel($userId){

        $statistics = file_get_contents($this->basepath . 'Statistics.json');
        $statistics = json_decode($statistics)->results;

        foreach($statistics as $statistic){

            if(isset($statistic->msuser->objectId) && isset($statistic->difficultyLevel) && ($statistic->msuser->objectId == $userId)){
                return $statistic->difficultyLevel;
            }

        }

        return 0;

    }

    private function userResolveDependencies($userId){

        $books = DB::table('books')
            ->get();

        $games = DB::table('games')
            ->get();

        $instruments = DB::table('shop_inventory')
            ->get();

        foreach($books as $book){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('user_books'),
                'user_id' => $userId,
                'book_id' => $book->id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('user_books')
            ->insert($preparedRows)
        ){
            die("Cannot populate user_books table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        foreach($games as $game){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('user_games'),
                'user_id' => $userId,
                'game_id' => $game->game_id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('user_games')
            ->insert($preparedRows)
        ){
            die("Cannot populate user_games table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

        foreach($instruments as $instrument){
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('user_instruments'),
                'user_id' => $userId,
                'instrument_id' => $instrument->id,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
        !DB::table('user_instruments')
            ->insert($preparedRows)
        ){
            die("Cannot populate user_instruments table");
        }else{
            //echo "success<br/>";
            unset($preparedRows);
        }

    }

    private function loadCinema(){
        $cinemas = file_get_contents($this->basepath . 'Cinema.json');
        $cinemas = json_decode($cinemas)->results;

        $preparedRows = array();
        foreach($cinemas as $cinema){
            if(
                !isset($cinema->objectId) ||
                !isset($cinema->msuser->objectId)
            ){
                continue;
            }
            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('cinema'),
                'user_id' => $cinema->msuser->objectId,
                'songId' => isset($cinema->songId) ? $cinema->songId : "",
                'songText' => isset($cinema->songTexts) ? $cinema->songTexts : "",
                'textText' => isset($cinema->textText) ? $cinema->textText : "",
                'textTime' => isset($cinema->textTime) ? $cinema->textTime : ""
            );
        }

        if(
            !DB::table('cinema')
                ->insert($preparedRows)
        ){
            die("Cannot populate cinema table");
        }
    }

    public function loadGameSessions(){
        $json = file_get_contents($this->basepath . "Log.json");
        $logs = json_decode($json)->results;

        $preparedRows = array();

        foreach($logs as $log){


            if(
                isset($log->objectId) &&
                isset($log->startDatetime->iso) &&
                isset($log->endDatetime->iso) &&
                isset($log->msuser->objectId) &&
                isset($log->msgame->objectId) &&
                isset($log->difficultyLevel)
            ){
                $preparedRows[] = array(
                    'id' => $log->objectId,
                    'session_startDatetime' => date("Y-m-d H:i:s", strtotime($log->startDatetime->iso)),
                    'session_endDatetime' => date("Y-m-d H:i:s", strtotime($log->endDatetime->iso)),
                    'user_id' => $log->msuser->objectId,
                    'game_id' => $log->msgame->objectId,
                    'score' => isset($log->score) ? $log->score : 0,
                    'difficulty_level' => $log->difficultyLevel,
                    'coins' => 0
                );
            }
        }

        if(
            !DB::table('game_sessions')
                ->insert($preparedRows)
        ){
            die("Cannot populate game_sessions table");
        }
    }

    public function users(){

        $this->loadBasicTables(); // asset_type + character + languages + role
        $this->loadBasicDependencies(); // books + games + instruments
        $this->loadCinema(); // cinema
        $this->loadGameSessions();

        // TO DO:

        $this->laodSchools();

        $users = file_get_contents($this->basepath . 'MSUser.json');
        $users = json_decode($users)->results;

        $preparedRows = array();

        $total_count = 1;

        $count = 0;

        foreach($users as $user){

            //echo "User number: " . $total_count++ . "<br/>";

            if(
                !isset($user->name) ||
                !isset($user->uniLoginUsername) ||
                !isset($user->msclass->objectId) ||
                !isset($user->role->objectId) ||
                ($user->role->objectId != '7PG6XCRphk' &&
                    $user->role->objectId != 'KWXgk5xtKg') ||
                !isset($user->mscharacter->objectId)

            ){
                continue;
            }

            $count++;
            echo $count;

            $name = explode(' ', $user->name);
            $first_name = $name[0];
            unset($name[0]);
            $last_name = implode(' ', $name);

            $preparedRows[] = array(
                'id' => $user->objectId,
                'first_name' => $first_name, // required
                'last_name' => $last_name, // required
                'username' => $user->uniLoginUsername, // required
                'password' => null,
                'coins' => isset($user->coins) ? $user->coins : 0,
                'level' => $this->getLevel($user->objectId),
                'founded_notes' => 0,
                'class_id' => $user->msclass->objectId, // required
                'role_id' => $user->role->objectId, // required
                'character_id' => isset($user->mscharacter->objectId) ? $user->mscharacter->objectId : null,
                'hall_stars' => 0
            );

            $this->userResolveDependencies($user->objectId);

        }

        if(
        !DB::table('users')
            ->insert($preparedRows)
        ){
            die("Cannot populate classes table");
        }else{
            //echo "success</br>";
        }

        $this->classes();

    }

    public function file($filename){
        $file = file_get_contents($this->basepath . $filename . ".json");
        $file = json_decode($file)->results;

        echo "<pre>";
        var_dump($file);
        echo "</pre>";
    }

    public function test(){
        $json = file_get_contents($this->basepath . "MSUser.json");
        $users = json_decode($json)->results;

        $json = file_get_contents($this->basepath . "MSClass.json");
        $classes = json_decode($json)->results;

        //echo "<table border = '1px solid gray'>";

        $count = 0;

        /*foreach($users as $user){
            if(
                !isset($user->mscharacter->objectId) ||
                !isset($user->role->objectId) ||
                !isset($user->objectId) ||
                !isset($user->msclass->objectId) ||
                !isset($user->uniLoginUsername)
            ){
                continue;
            }

            if($user->role->objectId == 'KWXgk5xtKg'){
                $count++;
            }
        }*/

        foreach($classes as $class){
            if(
                !isset($class->objectId)
            ){
                continue;
            }

            foreach($users as $user){
                if(
                    !isset($user->mscharacter->objectId) ||
                    !isset($user->role->objectId) ||
                    !isset($user->objectId) ||
                    !isset($user->msclass->objectId) ||
                    !isset($user->uniLoginUsername)
                ){
                    continue;
                }

                if($user->msclass->objectId == $class->objectId && ($user->role->objectId == "KWXgk5xtKg") && $user->uniLoginUsername == 'tine0888'){
                    echo $user->uniLoginUsername . "<br/>";
                    var_dump($user);
                    //$count++;
                    $className = isset($class->title) ? $class->title : "No name";
                    echo $className . " ( " . $class->msschool->objectId . " ) " . "               " . $user->name."<br/><br/>";
                    echo "<pre>";
                    var_dump($class);
                    echo "</pre>";
                }
            }
        }

        echo $count;

        /*foreach($classes as $class){
            if(
                !isset($class->objectId) ||
                !isset($class->msschool->objectId)
            ){
                continue;
            }

            if($class->msschool->objectId != 'zGOiydXCmr'){
                continue;
            }

            foreach($users as $user){
                if(
                    !isset($user->objectId) ||
                    !isset($user->msclass->objectId)
                ){
                    continue;
                }

                if($user->msclass->objectId == $class->objectId && $user->role->objectId = 'KWXgk5xtKg')
                    echo "<tr><td>" . $class->title . " ( " . $class->objectId . " ) </td><td>" . $user->name. " ( " . $user->objectId . " ) </td></tr><br/><br/>";
            }
        }*/

        echo "</table>";
    }
}
