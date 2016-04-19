<?php
/**
 * Created by PhpStorm.
 * User: Otinsoft
 * Date: 09.02.2016
 * Time: 17:57
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\User;
use App\Game;
use App\Cinema;
use App\Language;

class UserController extends Controller{

    private function parseSHA1 ($string)
    {
        $result = "";
        for($i = 0; $i < strlen($string); $i++){
            if($string[$i] === ' '){
                $result .= '+';
            }else{
                $result .= $string[$i];
            }
        }
        return $result;
    }

    private function sanitize($string){
        $newString = strip_tags($string);
        $newString = htmlspecialchars($newString);

        return $newString;
    }

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

    /**
     * Getters
     */

    private function checkIfExpired($time){
        $expire_date = strtotime($time);
        $now = time();

        if($now - $expire_date > 0){
            return true;
        }
        return false;
    }

    private function updateUserAccess($userId){
        $books = DB::table('user_books')
            ->where('user_id', $userId)
            ->get();

        foreach($books as $book){
            if($book->is_allowed == 1){
                continue;
            }
            if($this->checkIfExpired($book->allowed_endDatetime)){
                if(
                    !DB::table('user_books')
                        ->where('id', $book->id)
                        ->update([
                            'is_allowed' => 1,
                            'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                            'allowed_endDatetime' => date('Y-m-d H:i:s', 0),
                        ])
                ){
                    echo json_encode(array('success' => '04'));
                    die();
                }
            }
        }

        $instruments = DB::table('user_instruments')
            ->where('user_id', $userId)
            ->get();

        foreach($instruments as $instrument){
            if($instrument->is_allowed == 0){
                continue;
            }
            if($this->checkIfExpired($instrument->allowed_endDatetime)){
                if(
                    !DB::table('user_instruments')
                        ->where('id', $instrument->id)
                        ->update([
                            'is_allowed' => 0,
                            'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                            'allowed_endDatetime' => date('Y-m-d H:i:s', 0),
                        ])
                ){
                    echo json_encode(array('success' => '05'));
                    die();
                }
            }
        }

        $games = DB::table('user_games')
            ->where('user_id', $userId)
            ->get();

        foreach($games as $game){
            if($game->is_allowed == 1){
                continue;
            }
            if($this->checkIfExpired($game->allowed_endDatetime)){
                if(
                    !DB::table('user_games')
                        ->where('id', $game->id)
                        ->update([
                            'is_allowed' => 1,
                            'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                            'allowed_endDatetime' => date('Y-m-d H:i:s', 0),
                        ])
                ){
                    echo json_encode(array('success' => '06'));
                    die();
                }
            }
        }
    }

    private function updateClassAccess($classId){
        $books = DB::table('books')
            ->where('class_id', $classId)
            ->get();

        foreach($books as $book){
            if($book->is_allowed == 1){
                continue;
            }
            if($this->checkIfExpired($book->allowed_endDatetime)){
                if(
                    !DB::table('books')
                        ->where('id', $book->id)
                        ->update([
                            'is_allowed' => 1,
                            'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                            'allowed_endDatetime' => date('Y-m-d H:i:s', 0)
                        ])
                ){
                    echo json_encode(array('success' => '07'));
                    die();
                }
            }
        }

        $games = DB::table('class_games_access')
            ->where('class_id', $classId)
            ->get();

        foreach($games as $game){
            if($game->is_allowed == 1){
                continue;
            }
            if($this->checkIfExpired($game->allowed_endDatetime)){
                if(
                    !DB::table('class_games_access')
                        ->where('id', $game->id)
                        ->update([
                            'is_allowed' => 1,
                            'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                            'allowed_endDatetime' => date('Y-m-d H:i:s', 0)
                        ])
                ){
                    echo json_encode(array('success' => '08'));
                    die();
                }
            }
        }

        $instruments = DB::table('class_instruments_access')
            ->where('class_id', $classId)
            ->get();

        foreach($instruments as $instrument){
            if($instrument->is_allowed == 0){
                continue;
            }
            if($this->checkIfExpired($instrument->allowed_endDatetime)){
                if(
                !DB::table('class_instruments_access')
                    ->where('id', $instrument->id)
                    ->update([
                        'is_allowed' => 0,
                        'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                        'allowed_endDatetime' => date('Y-m-d H:i:s', 0)
                    ])
                ){
                    echo json_encode(array('success' => '09'));
                    die();
                }
            }
        }
    }

    public function login ()
    {
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));

        if(isset($_REQUEST['auth']))
            $auth = $_REQUEST['auth'];
        else{
            $auth = null;
        }
        
        if(isset($_REQUEST['timestamp'])){
            $timestamp = $_REQUEST['timestamp'];
        }else{
            $timestamp = null;
        }

        if(isset($_REQUEST['password'])){
            $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        }else{
            $password = md5($timestamp . 'ida780698fjl3' . $_REQUEST['username']);
            //var_dump($password);
            if($password == $_REQUEST['auth']){
                if(
                !DB::table('users')
                    ->where('username', $username)
                    ->update([
                        'password' => 'nopassword'
                    ])
                ){
                    echo json_encode(array("valid" => "01"));
                    die();
                }
                if(
                    !DB::table('users')
                    ->where('username', $username)
                    ->update([
                        'password' => $auth
                    ])
                ){
                    echo json_encode(array("valid" => "02"));
                    die();
                }
            }
        }
        if(isset($username) && isset($password)) {
            $user = DB::table('users')
                        ->where('username', $username)
                        ->where('password', $password)
                        ->whereIn('role_id', ['KWXgk5xtKg', '7PG6XCRphk'])
                        ->first();

            if (isset($user->id)) {

                $this->updateUserAccess($user->id);
                $this->updateClassAccess($user->class_id);

                echo json_encode(array('valid' => '1'));
                die();
            } else {
                echo json_encode(array('valid' => '03'));
                die();
            }
        }else{
            die("You forgot to insert username and/or password.");
        }
    }

    private function getUserId($username, $password){
        $user = null;
        $user = DB::table('users')
            ->where('username', $username)
            ->where('password', $password)
            ->first();

        if(isset($user->id)){
            return $user;
        }else{
            die("The user doesn't exist in DB");
        }
    }

    private function getUserGames($userId, $all = false){
        if($all){
            $games = DB::table('games')
                ->join('user_games', 'user_games.game_id', '=', 'games.game_id')
                ->where('user_games.user_id', $userId)
                ->get();

            return $games;
        }

        $games = DB::table('games')
            ->join('user_games', 'user_games.game_id', '=', 'games.game_id')
            ->where('user_games.user_id', $userId)
            ->where('is_allowed', 1)
            ->get();

        return $games;
    }

    public function getUser ()
    {
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        if(isset($username) && isset($password)) {
            $user = $this->getUserId($username, $password);

            $role = $user->role_id;
            $coins = $user->coins;
            $hall_stars = $user->hall_stars;
            $character = DB::table('users')
                ->join('character', 'users.character_id', '=', 'character.id')
                ->where('users.id', $user->id)
                ->value('character');
            $allowedGames = $this->getUserGames($user->id, false);
            $response = array();
            foreach ($allowedGames as $allowedGame) {
                $response["games"][] = $allowedGame->game_id;
            }
            $response['role'] = $role;
            $response['character'] = $character;
            $response['coins'] = $coins;
            $response['hallStars'] = $hall_stars;

            echo json_encode($response);
        }else{
            die("Unknown values");
        }
    }

    private function getLanguageId($language){
        $languageId = DB::table('languages')
            ->where('language', $language)
            ->value('id');

        return $languageId;
    }

    private function getUserInstruments($userId, $languageId){
        $instruments = DB::table('user_instruments')
            ->join('shop_inventory', 'user_instruments.instrument_id', '=', 'shop_inventory.instrument_id')
            ->join('instrument_assets', 'shop_inventory.instrument_id', '=', 'instrument_assets.instrument_id')
            ->where('user_instruments.user_id', $userId)
            ->where('instrument_assets.language_id', $languageId)
            /*->select([
                'shop_inventory.instrument_id',
                'instrument_assets.title',
                'instrument_assets.text',
                'shop_inventory.price',
                'user_instruments.is_allowed',
                'user_instruments.is_achieved',
            ])*/
            ->get();

        return $instruments;
    }

    public function getShopInventory ()
    {
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $language = $this->sanitize($_GET['languageId']);
        if(isset($username) && isset($password)) {
            //$userId = DB::table('users')->where('username', $username)->where('password', $password)->value('id');
            $user = $this->getUserId($username, $password);
            $languageId = $this->getLanguageId($language);
            $instruments = $this->getUserInstruments($user->id, $languageId);

            foreach ($instruments as $instrument) {
                $arr = array();
                $arr["instrumentId"] = (string)$instrument->instrument_id;
                $arr["title"] = $instrument->title;
                $arr["textDescription"] = str_replace('<b>', '', str_replace('</b>', '', $instrument->text));
                $arr["price"] = (string)$instrument->price;
                $arr["active"] = (string)$instrument->is_allowed;
                $arr["purchased"] = (string)$instrument->is_achieved;
                $response[] = $arr;
            }

            $response = json_encode($response);
            $this->removeStringSpecialChars($response);

            echo $response;
        }else{
            die("Unknown values");
        }
    }

    private function removeStringSpecialChars(&$string){
        $string = str_replace('\\n', '', $string);
        $string = str_replace('\\t', '', $string);
    }

    public function getMuseumSubAssets($assetId, $languageId){
        $subassets = DB::table('museum_sub_assets')
            ->where('destinatar_id', $assetId)
            ->where('language_id', $languageId)
            ->get();

        $response = array();

        foreach($subassets as $subasset) {

            $obj = new \stdClass();
            $obj->title = $subasset->title;
            $obj->subtitle = $subasset->subtitle;
            $obj->textDescription = $subasset->text;
            $obj->imageUrl = $subasset->image;
            $sounds = array(
                array(
                    "name" => $subasset->sound1_name,
                    "url" => $subasset->sound1_url,
                ),
                array(
                    "name" => $subasset->sound2_name,
                    "url" => $subasset->sound2_url,
                )
            );
            $obj->sounds = $sounds;

            $response[$subasset->box_number] = $obj;
        }

        return $response;

    }

    public function getMuseumAssets ()
    {
        $language = $this->sanitize($_GET['language']);
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));

        if(isset($username) && isset($password) && isset($language)) {
            $languageId = $this->getLanguageId($language);

            $assets = DB::table('museum_assets')
                ->get();

            $response = array();

            foreach($assets as $asset){
                $arr = array();
                $arr['title'] = $asset->name;
                $arr['boxNumber'] = $asset->placement;

                $arr['subassets'] = $this->getMuseumSubAssets($asset->id, $languageId);
                $response[] = $arr;
            }

            //var_dump($response);

            $response = json_encode($response);
            $this->removeStringSpecialChars($response);
            echo $response;

        }else{
            die("Unknown values");
        }
    }

    public function getUserBooks($userId, $languageId){
        $books = DB::table('user_books')
            ->join('books', 'user_books.book_id', '=', 'books.id')
            ->where('user_id', $userId)
            ->where('books.language_id', $languageId)
            ->get();

        return $books;
    }

    public function getBooks ()
    {
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $language = $this->sanitize($_GET['languageId']);

        if(isset($username) && isset($password) && isset($language)) {
            $user = $this->getUserId($username, $password);
            $languageId = $this->getLanguageId($language);

            $books = $this->getUserBooks($user->id, $languageId);

            foreach($books as $book){
                $arr = array();
                $arr['number'] = $book->box_number;
                $arr['isAllowed'] = $book->is_allowed;
                $arr['bookTitle'] = empty($book->book_title) ? 'Book ' . $book->box_number : $book->book_title;
                for($i = 0; $i < 6; $i++){
                    $title = 'title' . ($i+1);
                    $desc = 'desc' . ($i+1);
                    $arr[$title] = $book->$title;
                    $arr[$desc] = $book->$desc;
                }
                $response[] = $arr;
            }

            echo json_encode($response);
        }else{
            die("Unknown values");
        }
    }

    public function gamesStatistics(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        if(isset($username) && isset($password)) {
            $user = $this->getUserId($username, $password);
            $games = $this->getUserGames($user->id, true);

            foreach ($games as $game) {
                $arr = array();
                $arr["gameId"] = $game->game_id;
                $arr["difficultyLevel"] = $game->difficulty_level;
                $arr["gamesPlayed"] = $game->game_played;
                $arr["score"] = $game->score;
                $response[] = $arr;
            }
            echo json_encode($response);
        }else{
            die("Unknown values");
        }
    }

    public function getCinema(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        if(isset($username) && isset($password)) {
            /*$userData = DB::table('users')
                ->where('username', $username)
                ->where('password', $password)
                ->first(['id', 'class_id']);*/
            $user = $this->getUserId($username, $password);
            $cinemas = DB::table('cinema')->where("user_id", $user->id)->get();
            if ((count($cinemas) > 1) or (count($cinemas) < 0)) {
                die("Collision detected at cinema");
            }
            foreach ($cinemas as $cinema) {
                $response["textTime"] = $cinema->textTime;
                $response["textText"] = $cinema->textText;
                $response["songId"] = $cinema->songId;
                $response["songText"] = $cinema->songText;
            }
            echo json_encode($response);
        }else{
            die("Unknown values");
        }
    }

    public function getUserNote($classId){
        $noteId = DB::table('classes')
            ->where('id', $classId)
            ->first();

        if(isset($noteId->museum_note_id)){

            $note = DB::table('museum_notes')
                ->where('id', $noteId->museum_note_id)
                //->where('language_id', $languageId)
                ->first();

            $response = new \stdClass();

            $response->title = isset($note->title) ? $note->title : '';
            $response->text = isset($note->text_description) ? $note->text_description : '';
            $response->audiofile_name = isset($note->audiofile_name) ? $note->audiofile_name : '';
            $response->audiofile_url = isset($note->audiofile_url) ? $note->audiofile_url : '';
            $response->is_default = isset($note->is_default) ? $note->is_default : '';

            return $response;

        }

        return array();
    }

    public function getMuseumCustomNote(){
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        if(isset($username) && isset($password)) {
            $user = $this->getUserId($username, $password);
            $note = $this->getUserNote($user->class_id);

            $url = explode('/', $note->audiofile_url);
            $url = 'assets/' . $url[count($url) - 1];

            $response = array();
            $response["title"] = $note->title;
            $response["textDescription"] = $note->text;
            $response["audioFileUrl"] = $url;
            $response["isStandard"] = $note->is_default;

            echo json_encode($response);
        }else{
            die("Unknown values");
        }
    }

    public function getTvShowQuestions(){
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $language = $this->sanitize($_GET['language']);
        $difficultyLevel = $this->sanitize($_GET['difficultyLevel']);

        if(isset($username) && isset($password) && isset($language) && isset($difficultyLevel)) {
            $userData = DB::table('users')->where('username', $username)
                ->where('password', $password)
                ->first(['id', 'class_id']);

            if(!isset($userData->id)){
                die();
            }

            $languageId = DB::table('languages')->where('language', $language)
                ->value('id');

            $TVQuiz = DB::table('questions')
                ->where('difficulty_level', $difficultyLevel)
                ->where('language_id', $languageId)
                ->get();

            $response = array();
            foreach($TVQuiz as $question){
                $arr = array();
                $arr["title"] = $question->question;
                $arr["question"] = $question->question;
                $arr["questionAsset"] = $question->question_asset;
                $arr["correctAnswer"] = $question->correctAnswer;
                $arr["correctAnswerAsset"] = $question->correctAnswerAsset;
                $arr["wrongAnswer1"] = $question->wrongAnswer1;
                $arr["wrongAnswerAsset1"] = $question->wrongAnswer1Asset;
                $arr["wrongAnswer2"] = $question->wrongAnswer2;
                $arr["wrongAnswerAsset2"] = $question->wrongAnswer2Asset;
                $arr["wrongAnswer3"] = $question->wrongAnswer3;
                $arr["wrongAnswerAsset3"] = $question->wrongAnswer3Asset;
                $arr["questionType"] = $question->type;
                $response[] = $arr;
            }
            shuffle($response);
            $response = array_slice($response, 0, 11);
            $response = json_encode($response);
            $response = str_replace('\\/', '/', $response);
            echo $response;
        }else{
            die("Unknown values");
        }
    }

    /**
     * Setters
     */

    public function setCoinNumber(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $coinNumber = $this->sanitize($_REQUEST["coins"]);
        if(isset($username) && isset($password) && isset($coinNumber)) {
            if (DB::table('users')->where('username', $username)
                ->where('password', $password)
                ->update(['coins' => $coinNumber])) {
                echo json_encode(array("success" => "1"));
            } else {
                echo json_encode(array("success" => "0"));
            }
        }else{
            die("Unknown values");
        }
    }

    public function setMinigameData(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $gameId = $this->sanitize($_REQUEST["gameId"]);
        $gameScore = $this->sanitize($_REQUEST["score"]);
        $startDatetime = $this->sanitize($_REQUEST['start_Datetime']);
        $endDatetime = time();
        $difficultyLevel = $this->sanitize($_REQUEST['difficultyLevel']);
        $coins = $this->sanitize($_REQUEST['coins']);
        if(isset($username) && isset($password) && isset($gameId) && isset($gameScore) && isset($startDatetime) && isset($endDatetime) && isset($difficultyLevel) && isset($coins)) {
            $userData = DB::table('users')->where('username', $username)
                ->where('password', $password)
                ->first(['id', 'class_id']);

            $key = $this->generateUniqueKeyValue('game_sessions');

            if (!DB::table("game_sessions")
                ->where("game_sessions.user_id", $userData->id)
                ->where("game_sessions.game_id", $gameId)
                ->insert(['id' => $key,
                    'user_id' => $userData->id,
                    'session_startDatetime' => date("Y-m-d H:i:s", $startDatetime),
                    'session_endDatetime' => date("Y-m-d H:i:s", $endDatetime),
                    'score' => $gameScore,
                    'game_id' => $gameId,
                    'difficulty_level' => $difficultyLevel,
                    'coins' => $coins]))
            {
                echo json_encode(array("success" => "0"));
                die();
            }

            $rowCount = DB::table("user_games")
                ->where("user_games.user_id", $userData->id)
                ->where("user_games.game_id", $gameId)
                ->first();

            if($rowCount){
                if (!DB::table("user_games")
                    ->where("user_games.user_id", $userData->id)
                    ->where("user_games.game_id", $gameId)
                    ->update([
                        'score' => $gameScore,
                        'difficulty_level' => $difficultyLevel,
                        'game_played' => $rowCount->game_played+1,
                    ]))
                {
                    echo json_encode(array("success" => "0"));
                    die();
                }
            }else{

                if (!DB::table("user_games")
                    ->where("user_games.user_id", $userData->id)
                    ->where("user_games.game_id", $gameId)
                    ->insert([
                        'id' => $this->generateUniqueKeyValue("user_games"),
                        'user_id' => $userData->id,
                        'game_id' => $gameId,
                        'difficulty_level' => $difficultyLevel,
                        'score' => $gameScore,
                        'game_played' => 1,
                        'is_allowed' => 1,
                        'allowed_startDatetime' => date('Y-m-d H:i:s', 0),
                        'allowed_endDatetime' => date('Y-m-d H:i:s', 0)
                    ])) {
                    echo json_encode(array("success" => "0"));
                    die();
                }
            }
            echo json_encode(array("success" => "1"));
        }else{
            die("Unknown values");
        }
    }

    public function setMusicHallStars(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $hallStars = $this->sanitize($_REQUEST["starNr"]);
        if(isset($username) && isset($password) && isset($hallStars)) {
            if (DB::table('users')
                ->where('username', $username)
                ->where('password', $password)
                ->update(['hall_stars' => $hallStars])) {
                echo json_encode(array("success" => "1"));
            } else {
                echo json_encode(array("success" => "0"));
            }
        }else{
            die("Unknown values");
        }
    }

    public function setCharacter(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $characterId = $this->sanitize($_REQUEST["characterId"]);

        if(isset($username) && isset($password) && isset($characterId)) {

            if (
                DB::table('users')->where('username', $username)
                    ->where('password', $password)
                    ->update([
                        'character_id' => $characterId,
                    ])
            ) {
                echo json_encode(array("success" => "1"));
                die();
            } else {
                echo json_encode(array("success" => "0"));
                die();
            }

        }else{
            die("Unknown values");
        }
    }

    public function setCinema(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $textText = $this->sanitize($_REQUEST["textText"]);
        $textTime = $this->sanitize($_REQUEST["textTime"]);
        $songId = $this->sanitize($_REQUEST["songIds"]);
        $songText = $this->sanitize($_REQUEST["songText"]);
        if(isset($username) && isset($password) && isset($textTime) && isset($textText) && isset($songId) && isset($songText)) {
            $userData = DB::table('users')
                ->leftJoin('cinema', 'cinema.user_id', '=', 'users.id')
                ->where('username', $username)
                ->where('password', $password)
                ->select('users.id', 'cinema.user_id', 'textText', 'textTime', 'songText', 'songId')
                ->first();

            if(isset($userData->user_id)){
                if(
                    DB::table('cinema')
                        ->where("user_id", $userData->id)
                        ->update([
                        'textText' => $textText,
                        'textTime' => $textTime,
                        'songId' => $songId,
                        'songText' => $songText,
                    ])
                ){
                    echo json_encode(array("success" => "1"));
                } else {
                    die(json_encode(array("success" => "0")));
                }
            }else{
                $key = $this->generateUniqueKeyValue('cinema');
                if (
                    DB::table('cinema')->insert([
                        'id' => $key,
                        'user_id' => $userData->id,
                        'textText' => $textText,
                        'textTime' => $textTime,
                        'songId' => $songId,
                        'songText' => $songText,
                ])) {
                    echo json_encode(array("success" => "1"));
                } else {
                    die(json_encode(array("success" => "0")));
                }
            }
        }else{
            die("Unknown values");
        }
    }

    public function setInstrumentPurchased(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST["username"]));
        $password = $this->parseSha1($this->sanitize($_REQUEST["password"]));
        $instrumentId = $this->sanitize($_REQUEST["instrumentId"]);

        if(isset($username) && isset($password) && isset($instrumentId)) {
            $userData = DB::table('users')
                ->where('username', $username)
                ->where('password', $password)
                ->first(['id', 'class_id']);

            $user_instrument_exists = DB::table('user_instruments')
                ->where('user_id', $userData->id)
                ->where('instrument_id', $instrumentId)
                ->count();

            if($user_instrument_exists){
                if(
                DB::table('user_instruments')
                    ->where('user_id', $userData->id)
                    ->where('instrument_id', $instrumentId)
                    ->update([
                        'is_achieved' => true,
                    ])
                ){
                    echo json_encode(array("success" => "1"));
                }else{
                    echo json_encode(array("success" => "0"));
                    die();
                }
            }else{
                echo json_encode(array("success" => "0"));
                die();
            }
        }else{
            die("Unknown values");
        }
    }

};
