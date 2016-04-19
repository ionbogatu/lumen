<?php
/**
 * Created by PhpStorm.
 * User: Otinsoft
 * Date: 23.02.2016
 * Time: 17:57
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TeacherController extends Controller{

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

    private function checkTeacherIfExists($username, $password){
        $teachers = DB::table('users')
            ->where('username', $username)
            ->where('password', $password)
            ->where('role_id', 'KWXgk5xtKg')
            ->get();
        if(count($teachers) == 0){
            die("Wrong username");
        }else if(count($teachers) == 1){
            return true;
        }else{
            die("Collision detected");
        }
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

    private function getLanguageId($language){
        $languageId = DB::table('languages')
            ->where('language', $language)
            ->value('id');

        return $languageId;
    }

    private function getClassName($classId){
        $className = DB::table('classes')
            ->where('id', $classId)
            ->value('name');

        return $className;
    }

    /**
     * Getters
     */

    public function getClassList ()
    {
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        if(isset($username) && isset($password)) {
            if ($this->checkTeacherIfExists($username, $password)) {
                $teacher = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->first();

                $classes = DB::table('teacher_classes')
                    ->where('teacher_classes.teacher_id', $teacher->id)
                    ->get();

                foreach($classes as $class){
                    $arr = array();
                    $arr['classId'] = $class->class_id;
                    $arr['className'] = $this->getClassName($class->class_id);
                    $response[] = $arr;
                }
            }

            echo json_encode($response);

        }else{
            die("You forgot to insert username and/or password.");
        }
    }

    public function getUserList(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        if(isset($username) && isset($password) && isset($classId)) {
            if ($this->checkTeacherIfExists($username, $password)) {
                $users = DB::table('users')
                    ->where('class_id', $classId)
                    ->where('role_id', '7PG6XCRphk')
                    ->get();
                foreach ($users as $user) {
                    $arr['id'] = $user->id;
                    $arr['name'] = $user->name;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getUserScores(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $userId = $this->sanitize($_GET['userId']);
        if(isset($userId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $playedGames = DB::table('user_games')
                    ->where('user_id', $userId)
                    ->join('games', 'user_games.game_id', '=', 'games.game_id')
                    ->select('games.title', 'user_games.score as score')
                    ->get();
                foreach ($playedGames as $playedGame) {
                    $arr = array();
                    $arr["gameName"] = $playedGame->title;
                    $arr["gameScore"] = $playedGame->score;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getUserLevels(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $userId = $this->sanitize($_GET['userId']);
        if(isset($userId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $playedGames = DB::table('user_games')
                    ->where('user_id', $userId)
                    ->join('games', 'user_games.game_id', '=', 'games.game_id')
                    ->select('games.title', 'user_games.difficulty_level as level')
                    ->get();

                foreach ($playedGames as $playedGame) {
                    $arr = array();
                    $arr["gameName"] = $playedGame->title;
                    $arr["gameLevel"] = $playedGame->level;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getUserTodayPlaytime(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $userId = $this->sanitize($_GET['userId']);
        if(isset($userId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $midnight = strtotime("today midnight");
                $playedGames = DB::select('select game_sessions.session_startDatetime as start, session_endDatetime as end, games.title from `games` join game_sessions on games.game_id = game_sessions.game_id where game_sessions.user_id = ? and game_sessions.session_endDatetime >= CURRENT_DATE', [$userId]);
                /*$playedGames = DB::table('game_sessions')
                    ->join('games', 'game_sessions.game_id', '=', 'games.game_id')
                    ->where('game_sessions.user_id', $userId)
                    ->get();*/

                foreach ($playedGames as $playedGame) {
                    $arr = array();
                    $arr["gameName"] = $playedGame->title;
                    $gameStarted = strtotime($playedGame->start);
                    $gameEnded = strtotime($playedGame->end);
                    if($gameStarted <= $midnight){
                        $arr["gameTime"] = $gameEnded - $midnight;
                    }else{
                        $arr["gameTime"] = $gameEnded - $gameStarted;
                    }
                    $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getUserTotalPlaytime(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $userId = $this->sanitize($_GET['userId']);
        if(isset($userId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $playedGames = DB::table('game_sessions')
                    ->where('user_id', $userId)
                    ->join('games', 'game_sessions.game_id', '=', 'games.game_id')
                    ->select('games.game_id', 'game_sessions.game_id', 'games.title', 'game_sessions.session_startDatetime as start', 'game_sessions.session_endDatetime as end', DB::raw('SUM(TIMESTAMPDIFF(SECOND,`game_sessions`.`session_startDatetime`,`game_sessions`.`session_endDatetime`)) as dateDiff'))
                    ->groupBy('game_sessions.game_id')
                    ->get();
                foreach ($playedGames as $playedGame) {
                    $arr = array();
                    $arr["gameName"] = $playedGame->title;
                    $arr["gameId"] = $playedGame->game_id;
                    $arr["gameTime"] = $playedGame->dateDiff;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getClassScoreIndividual(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $users = DB::select('select name, SUM(score) as score from users join user_games on users.id = user_games.user_id where users.class_id = ? and users.role_id = ? group by name', [$classId, '7PG6XCRphk']);
                foreach($users as $user){
                    $arr = array();
                    $arr["userName"] = $user->name;
                    $arr["userScore"] = $user->score;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getClassScoreTotal(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $users = DB::table('users')
                    ->join('user_games', 'users.id', '=', 'user_games.user_id')
                    ->join('games', 'user_games.game_id', '=', 'games.game_id')
                    ->where('class_id', $classId)
                    ->where('users.role_id', '7PG6XCRphk')
                    ->select(['user_games.game_id', 'games.title', DB::raw('SUM(user_games.score) as commonScore')])
                    ->groupBy('user_games.game_id')
                    ->get();
                foreach($users as $user){
                    $arr = array();
                    $arr["gameTitle"] = $user->title;
                    $arr["gameScore"] = $user->commonScore;
                    $response[] = $arr;
                }
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getClassInstrumentAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        $languageId = $this->sanitize($_GET['languageId']);
        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {

                $languageId = DB::table('languages')->where('language', $languageId)->value('id');

                $instruments = DB::table('class_instruments_access')
                    ->join('shop_inventory', 'class_instruments_access.instrument_id', '=', 'shop_inventory.instrument_id')
                    ->join('instrument_assets', 'instrument_assets.instrument_id', '=', 'shop_inventory.instrument_id')
                    ->where('class_id', $classId)
                    ->where('instrument_assets.language_id', $languageId)
                    ->select([
                        'class_instruments_access.is_allowed',
                        'instrument_assets.title',
                        'shop_inventory.instrument_id'])
                    ->get();

                foreach($instruments as $instrument){
                    $arr = array();
                    $arr["instrumentId"] = $instrument->instrument_id;
                    $arr["instrumentName"] = $instrument->title;
                    $arr["isAllowed"] = $instrument->is_allowed;
                    $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getClassBookAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                /*$teacherId = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->where('role_id', 'KWXgk5xtKg')
                    ->value('id');*/

                $books = DB::table('books')
                            //->join('books', 'class_books_access.book_id', '=', 'books.id')
                            ->where('class_id', $classId)
                            //->where('teacher_id', $teacherId)
                            ->get();

                foreach($books as $book){
                    $arr = array();
                    $arr["bookId"] = $book->id;
                    $arr["bookTitle"] = empty($book->book_title) ? 'Book ' . $book->box_number : $book->book_title;
                    $arr["isAllowed"] = $book->is_allowed;
                    $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values1");
            }
        }else{
            die("Unknown values2");
        }
    }

    public function getClassMinigameAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        if(isset($classId) && isset($username) && isset($password)){

            if ($this->checkTeacherIfExists($username, $password)) {

                $games = DB::select("select * from class_games_access join games on class_games_access.game_id = games.game_id where class_games_access.class_id = ?", [$classId]);

                foreach($games as $game){
                    $arr = array();
                    $arr["gameId"] = $game->game_id;
                    $arr["gameTitle"] = $game->title;
                    $arr["isAllowed"] = $game->is_allowed;
                    $response[] = $arr;
                }

                /*echo "<pre>";
                var_dump($response);
                echo "</pre>";*/

                echo json_encode($response);
            }else{
                die("Unknown values1");
            }
        }else{
            die("Unknown values2");
        }
    }

    public function getStudentInstrumentAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $studentId = $this->sanitize($_GET['studentId']);
        if(isset($studentId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {

                $instruments = DB::table('user_instruments')
                    ->where('user_instruments.user_id', $studentId)
                    ->get();

                foreach($instruments as $instrument){
                        $arr = array();
                        $arr["instrumentId"] = $instrument->instrument_id . ''; // convert to string
                        $arr["active"] = (int)$instrument->is_allowed."";
                        $arr["purchased"] = (int)$instrument->is_achieved."";
                        $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values2");
            }
        }else{
            die("Unknown values1");
        }
    }

    public function getStudentBookAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $studentId = $this->sanitize($_GET['studentId']);
        $languageId = $this->sanitize($_GET['languageId']);
        if(isset($studentId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                /*$teacherId = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->where('role_id', 'KWXgk5xtKg')
                    ->value('id');*/

                $languageId = DB::table('languages')
                                ->where('language', $languageId)
                                ->value('id');

                $books = DB::table('user_books')
                    ->join('books', 'user_books.book_id', '=', 'books.id')
                    ->where('books.language_id', $languageId)
                    ->where('user_books.user_id', $studentId)
                    //->where('books.teacher_id', $teacherId)
                    ->select(
                        'books.id',
                        'books.box_number',
                        'books.book_title',
                        'user_books.is_allowed'
                    )
                    ->get();

                foreach($books as $book){
                        $arr = array();
                        $arr["bookId"] = $book->id . ''; // convert to string string
                        $arr["boxNumber"] = $book->box_number;
                        $arr["bookTitle"] = empty($book->book_title) ? 'Book ' . $book->box_number : $book->book_title;
                        $arr["isAllowed"] = (int)$book->is_allowed."";
                        $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getStudentMinigameAccess(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $studentId = $this->sanitize($_GET['studentId']);
        if(isset($studentId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {

                $games = DB::table('user_games')
                    ->join('games', 'games.game_id', '=', 'user_games.game_id')
                    ->where('user_games.user_id', $studentId)
                    ->get();

                foreach($games as $game){
                        $arr = array();
                        $arr["gameId"] = $game->game_id . ''; // convert to string
                        $arr["gameTitle"] = $game->title;
                        $arr["isAllowed"] = (int)$game->is_allowed."";
                        $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getTeacherMuseumCustomBooks(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);

        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                /*$teacher_id = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->value('id');*/

                $customBooks = DB::table('books')
                    ->where('class_id', $classId)
                    //->where('books.is_allowed', true)
                    //->where('teacher_id', $teacher_id)
                    ->get();

                foreach($customBooks as $book){
                    $arr = array();
                    $arr['number'] = $book->box_number;
                    $arr['bookTitle'] = empty($book->book_title) ? 'Book ' . $book->box_number : $book->book_title;
                    $arr['title1'] = $book->title1;
                    $arr['desc1'] = $book->desc1;
                    $arr['title2'] = $book->title2;
                    $arr['desc2'] = $book->desc2;
                    $arr['title3'] = $book->title3;
                    $arr['desc3'] = $book->desc3;
                    $arr['title4'] = $book->title4;
                    $arr['desc4'] = $book->desc4;
                    $arr['title5'] = $book->title5;
                    $arr['desc5'] = $book->desc5;
                    $arr['title6'] = $book->title6;
                    $arr['desc6'] = $book->desc6;
                    $response[] = $arr;
                }
                //$response = array_values($response);
                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function getSelectedFlag($classId, $noteId){
        $note_id = DB::table('classes')
            ->where('classes.id', $classId)
            ->value('classes.museum_note_id');

        if($note_id == $noteId){
            return "1";
        }

        return "0";
    }

    public function getTeacherMuseumCustomNote(){

        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);
        $language = $this->sanitize($_GET['languageId']);

        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                /*$default = DB::table('museum_notes')
                    ->whereNull('class_id')
                    ->orWhere('class_id', '');*/
                $languageId = $this->getLanguageId($language);
                $museum_notes = DB::select('select * from museum_notes where (class_id is null or class_id like "" or class_id = ?) and (language_id = ? or language_id is null)', [$classId, $languageId]);

                foreach($museum_notes as $museum_note){
                    $arr = array();
                    $arr['id'] = $museum_note->id;
                    $arr['title'] = $museum_note->title;
                    $arr['textDescription'] = $museum_note->text_description;
                    $arr['isStandard'] = ''.$museum_note->is_default;
                    $arr['isSelected'] = $this->getSelectedFlag($classId, $museum_note->id);
                    $arr['soundId'] = isset($museum_note->audiofile_name) ? $museum_note->audiofile_name : "";
                    $arr['soundUrl'] = isset($museum_note->audiofile_url) ? $museum_note->audiofile_url : "";
                    $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function getTvShowData(){
        $response = array();
        $username = $this->parseSHA1($this->sanitize($_GET['username']));
        $password = $this->parseSHA1($this->sanitize($_GET['password']));
        $classId = $this->sanitize($_GET['classId']);

        if(isset($classId) && isset($username) && isset($password)){
            if ($this->checkTeacherIfExists($username, $password)) {
                $teacher = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->first(['id']);
                $class = DB::table('classes')
                    ->where('id', $classId)
                    ->first(['id', 'use_default_questions', 'use_custom_questions']);
                $response['activatePremade'] = $class->use_default_questions;
                $response['activateYourOwn'] = $class->use_custom_questions;

                $custom_questions = DB::table('class_custom_questions')
                    ->where('teacher_id', $teacher->id)
                    ->where('class_id', $classId)
                    ->get();

                foreach($custom_questions as $custom_question){
                    $arr = array();
                    $arr['question'] = $custom_question->question;
                    $arr['correctAnswer'] = $custom_question->correct_answer;
                    $arr['wrongAnswer1'] = $custom_question->wrong_answer1;
                    $arr['wrongAnswer2'] = $custom_question->wrong_answer2;
                    $arr['wrongAnswer3'] = $custom_question->wrong_answer3;
                    $arr['difficultyLevel'] = $custom_question->difficulty_level;
                    $arr['questionId'] = $custom_question->id;
                    $response[] = $arr;
                }

                echo json_encode($response);
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    /**
     * Setters
     */

    public function setClassId(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                $class_id = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->where('role_id', 'KWXgk5xtKg')
                    ->value('class_id');

                if($class_id === $classId){
                    echo json_encode(array("success" => "1"));
                    die();
                }

                if(
                    !DB::table('users')
                        ->where('username', $username)
                        ->where('password', $password)
                        ->where('role_id', 'KWXgk5xtKg')
                        ->update([
                            'class_id' => $classId
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }
                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function setStudentMinigameRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $studentId = $this->sanitize($_REQUEST['studentId']);
        $gameId = $this->sanitize($_REQUEST['minigameId']);
        $gameAllowed = $this->sanitize($_REQUEST['gameAllowed']);
        $time = $this->sanitize($_REQUEST['time']);

        if(
            isset($username) &&
            isset($password) &&
            isset($studentId) &&
            isset($gameId) &&
            isset($gameAllowed) &&
            isset($time)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($gameAllowed == 1){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                if(
                    !DB::table('user_games')
                        ->where('game_id', $gameId)
                        ->where('user_id', $studentId)
                        ->update([
                            'is_allowed' => $gameAllowed,
                            'allowed_startDatetime' => $startDatetime,
                            'allowed_endDatetime' => $endDatetime
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));

            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function setStudentInstrumentRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $studentId = $this->sanitize($_REQUEST['studentId']);
        $instrumentId = $this->sanitize($_REQUEST['instrumentId']);
        $time = $this->sanitize($_REQUEST['time']);
        $instrumentAllowed = $this->sanitize($_REQUEST['instrumentAllowed']);

        if(
            isset($username) &&
            isset($password) &&
            isset($studentId) &&
            isset($instrumentId) &&
            isset($time) &&
            isset($instrumentAllowed)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($instrumentAllowed == 0){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                if(
                    !DB::table('user_instruments')
                        ->where('instrument_id', $instrumentId)
                        ->where('user_id', $studentId)
                        ->update([
                            'is_allowed' => $instrumentAllowed,
                            'allowed_startDatetime' => $startDatetime,
                            'allowed_endDatetime' => $endDatetime
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function setStudentBookRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $studentId = $this->sanitize($_REQUEST['studentId']);
        $bookId = $this->sanitize($_REQUEST['bookId']);
        $bookAllowed = $this->sanitize($_REQUEST['bookAllowed']);
        $time = $this->sanitize($_REQUEST['time']);

        if(
            isset($username) &&
            isset($password) &&
            isset($studentId) &&
            isset($bookId) &&
            isset($bookAllowed) &&
            isset($time)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($bookAllowed == 1){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                if(
                !DB::table('user_books')
                    ->where('book_id', $bookId)
                    ->where('user_id', $studentId)
                    ->update([
                        'is_allowed' => $bookAllowed,
                        'allowed_startDatetime' => $startDatetime,
                        'allowed_endDatetime' => $endDatetime
                    ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function setStudentAllowedAllInstruments(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $studentId = $this->sanitize($_REQUEST['studentId']);
        $isAllowed = $this->sanitize($_REQUEST['isAllowed']);

        if(
            isset($username) &&
            isset($password) &&
            isset($studentId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                $instruments = DB::table('shop_inventory')
                    ->get();

                foreach($instruments as $instrument){
                    $is_allowed = DB::table('user_instruments')
                        ->where('user_id', $studentId)
                        ->where('instrument_id', $instrument->instrument_id)
                        ->value('is_allowed');
                    if($is_allowed == 1){
                        continue;
                    }

                    if(
                        !DB::table('user_instruments')
                            ->where('user_id', $studentId)
                            ->update([
                                'is_allowed' => $isAllowed,
                                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                                'allowed_endDatetime' => date("Y-m-d H:i:s", 0)
                            ])
                    ){
                        echo json_encode(array("success" => "0"));
                        die();
                    }
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function updateUserMinigameRestriction($classId, $minigameId, $gameAllowed, $time){
        $users = DB::table('users')
            ->where('users.class_id', $classId)
            ->get();

        if($gameAllowed == 1){
            $start = date("Y-m-d H:i:s", 0);
            $end = date("Y-m-d H:i:s", 0);
        }else{
            $start = date("Y-m-d H:i:s", time());
            $end = date("Y-m-d H:i:s", intval($time));
        }

        foreach($users as $user){
            $user_game_access = DB::table('user_games')
                ->where('user_id', $user->id)
                ->where('game_id', $minigameId)
                ->first();

            if($user_game_access->is_allowed == $gameAllowed){
                continue;
            }

            if(
                !DB::table('user_games')
                    ->where('user_id', $user->id)
                    ->where('game_id', $minigameId)
                    ->update([
                        'is_allowed' => $gameAllowed,
                        'allowed_startDatetime' => $start,
                        'allowed_endDatetime' => $end
                    ])
            ){
                echo json_encode(array('success' => '0'));
                die();
            }
        }
    }

    public function setClassMinigameRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']); // is not necessary;
        $minigameId = $this->sanitize($_REQUEST['minigameId']);
        $time = $this->sanitize($_REQUEST['time']);
        $gameAllowed = $this->sanitize($_REQUEST['gameAllowed']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($minigameId) &&
            isset($gameAllowed) &&
            isset($time)
        ) {

            if ($this->checkTeacherIfExists($username, $password)) {
                if($gameAllowed == 1){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                $this->updateUserMinigameRestriction($classId, $minigameId, $gameAllowed, $time);

                if(
                    !DB::table('class_games_access')
                        ->where('game_id', $minigameId)
                        ->where('class_id', $classId)
                        ->update([
                            'is_allowed' => $gameAllowed,
                            'allowed_startDatetime' => $startDatetime,
                            'allowed_endDatetime' => $endDatetime
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function updateUserInstrumentRestriction($classId, $instrumentId, $instrumentAllowed, $time){
        $users = DB::table('users')
            ->where('users.class_id', $classId)
            ->get();

        if($instrumentAllowed == 0){
            $start = date("Y-m-d H:i:s", 0);
            $end = date("Y-m-d H:i:s", 0);
        }else{
            $start = date("Y-m-d H:i:s", time());
            $end = date("Y-m-d H:i:s", intval($time));
        }

        foreach($users as $user){
            $user_instrument_access = DB::table('user_instruments')
                ->where('user_id', $user->id)
                ->where('instrument_id', $instrumentId)
                ->first();

            if($user_instrument_access->is_allowed == $instrumentAllowed){
                continue;
            }

            if(
                !DB::table('user_instruments')
                    ->where('user_id', $user->id)
                    ->where('instrument_id', $instrumentId)
                    ->update([
                        'is_allowed' => $instrumentAllowed,
                        'allowed_startDatetime' => $start,
                        'allowed_endDatetime' => $end
                    ])
            ){
                echo json_encode(array('success' => '0'));
                die();
            }
        }
    }

    public function setClassInstrumentRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $instrumentId = $this->sanitize($_REQUEST['instrumentId']);
        $time = $this->sanitize($_REQUEST['time']);
        $instrumentAllowed = $this->sanitize($_REQUEST['instrumentAllowed']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($instrumentId) &&
            isset($instrumentAllowed) &&
            isset($time)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($instrumentAllowed == 0){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                $this->updateUserInstrumentRestriction($classId, $instrumentId, $instrumentAllowed, $time);

                if(
                    !DB::table('class_instruments_access')
                        ->where('instrument_id', $instrumentId)
                        ->where('class_id', $classId)
                        ->update([
                            'is_allowed' => $instrumentAllowed,
                            'allowed_startDatetime' => $startDatetime,
                            'allowed_endDatetime' => $endDatetime
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function updateUserBookRestriction($classId, $bookId, $bookAllowed, $time){
        $users = DB::table('users')
            ->where('users.class_id', $classId)
            ->get();

        if($bookAllowed == 1){
            $start = date("Y-m-d H:i:s", 0);
            $end = date("Y-m-d H:i:s", 0);
        }else{
            $start = date("Y-m-d H:i:s", time());
            $end = date("Y-m-d H:i:s", intval($time));
        }

        foreach($users as $user){
            //var_dump($user);
            $user_book_access = DB::table('user_books')
                ->where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            //var_dump($user_book_access);

            if($user_book_access->is_allowed == $bookAllowed){
                continue;
            }

            if(
            !DB::table('user_books')
                ->where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->update([
                    'is_allowed' => $bookAllowed,
                    'allowed_startDatetime' => $start,
                    'allowed_endDatetime' => $end
                ])
            ){
                echo json_encode(array('success' => '0'));
                die();
            }
        }
    }

    public function setClassBookRestriction(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $bookId = $this->sanitize($_REQUEST['bookId']);
        $bookAllowed = $this->sanitize($_REQUEST['bookAllowed']);
        $time = $this->sanitize($_REQUEST['time']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($bookId) &&
            isset($bookAllowed) &&
            isset($time)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($bookAllowed == 1){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                $this->updateUserBookRestriction($classId, $bookId, $bookAllowed, $time);

                if(
                    !DB::table('books')
                        //->where('class_id', $classId)
                        ->where('id', $bookId)
                        ->update([
                            'is_allowed' => $bookAllowed,
                            'allowed_startDatetime' => $startDatetime,
                            'allowed_endDatetime' => $endDatetime
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function updateUserInstrumentAllowAll($classId, $instrumentId, $isAllowed, $time){
        $users = DB::table('users')
            ->where('users.class_id', $classId)
            ->get();

        if($isAllowed == 0){
            $startDatetime = date("Y-m-d H:i:s", 0);
            $endDatetime = date("Y-m-d H:i:s", 0);
        }else{
            $startDatetime = date("Y-m-d H:i:s", time());
            $endDatetime = date("Y-m-d H:i:s", intval($time));
        }

        foreach($users as $user){

            $user_instrument_access = DB::table('user_instruments')
                ->where('user_id', $user->id)
                ->where('instrument_id', $instrumentId)
                ->first();

            if($user_instrument_access->is_allowed == $isAllowed){
                continue;
            }

            if(
                !DB::table('user_instruments')
                    ->where('user_id', $user->id)
                    ->where('instrument_id', $instrumentId)
                    ->update([
                        'is_allowed' => intval($isAllowed),
                        'allowed_startDatetime' => $startDatetime,
                        'allowed_endDatetime' => $endDatetime
                    ])
            ){
                echo json_encode(array('success' => '02'));
                die();
            }
        }
    }

    public function setClassAllowedAllInstruments(){
        $start = time();

        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $isAllowed = $this->sanitize($_REQUEST['isAllowed']);
        $time = $this->sanitize($_REQUEST['time']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if($isAllowed == 0){
                    $startDatetime = date("Y-m-d H:i:s", 0);
                    $endDatetime = date("Y-m-d H:i:s", 0);
                }else{
                    $startDatetime = date("Y-m-d H:i:s", time());
                    $endDatetime = date("Y-m-d H:i:s", intval($time));
                }

                $instruments = DB::table('shop_inventory')
                    ->get();

                foreach($instruments as $instrument){
                    $is_allowed = DB::table('class_instruments_access')
                        ->where('class_id', $classId)
                        ->where('instrument_id', $instrument->instrument_id)
                        //->where('is_allowed', '!=', $isAllowed)
                        ->value('is_allowed');

                    //echo "update for instrument: <br/><pre>";
                    //var_dump($instrument->instrument_id);

                    //var_dump($is_allowed . " " . $isAllowed);

                    $this->updateUserInstrumentAllowAll($classId, $instrument->instrument_id, $isAllowed, $time);

                    if($is_allowed == $isAllowed){
                        continue;
                    }

                    echo "Insert into class_instruments_access <br/>";

                    if(
                        !DB::table('class_instruments_access')
                            ->where('class_id', $classId)
                            ->update([
                                'is_allowed' => intval($isAllowed),
                                'allowed_startDatetime' => $startDatetime,
                                'allowed_endDatetime' => $endDatetime
                            ])
                    ){
                        echo json_encode(array("success" => "01"));
                        die();
                    }
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }

        $end = time();

        echo $end - $start;
    }

    public function setTVShowSettings(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $activatePremade = $this->sanitize($_REQUEST['activatePremade']);
        $activateYourOwn = $this->sanitize($_REQUEST['activateYourOwn']);
        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($activatePremade) &&
            isset($activateYourOwn)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if(
                    !DB::table('classes')
                        ->where('id', $classId)
                        ->update([
                            'use_default_questions' => $activatePremade,
                            'use_custom_questions' => $activateYourOwn,
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }else{
                    echo json_encode(array("success" => "1"));
                }
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    private function resolveBookDependencies($bookId, $classId){
        $users = DB::table('users')
            ->where('class_id', $classId)
            ->get();

        $preparedRows = array();

        foreach($users as $user){
            $preparedRows[] = array(
                'id' => null,
                'user_id' => $user->id,
                'book_id' => $bookId,
                'is_allowed' => true,
                'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
            );
        }

        if(
            !DB::table('user_books')
                ->insert($preparedRows)
        ){
            echo json_encode(array('success' => '0'));
            die();
        }

        /*var_dump($preparedRows);
        die('ok');*/

        /*if(
            !DB::table('books')
                ->insert([
                    'id' => $this->generateUniqueKeyValue('books'),
                    'class_id' => $classId,
                    'book_id' => $bookId,
                    'is_allowed' => true,
                    'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                    'allowed_endDatetime' => date("Y-m-d H:i:s", 0)
                ])
        ){
            echo json_encode(array('success' => '0'));
            die();
        }*/
    }

    public function setBook()
    {
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $box_number = $this->sanitize($_REQUEST['boxNumber']);
        $book_title = $this->sanitize($_REQUEST['bookTitle']);
        $language = $this->sanitize($_REQUEST['language']);
        $new_book = $this->sanitize($_REQUEST['newBook']);

        $languageId = DB::table('languages')
                            ->where('language', $language)
                            ->value('id');

        $title = "title";
        $desc = "desc";
        $titles = array();
        $descs = array();
        for($i = 1;  $i <= 6; $i++){
            $vtitle = $title . $i;
            $vdesc = $desc . $i;
            $titles[$i-1] = isset($_REQUEST[$vtitle]) ? $_REQUEST[$vtitle] : "";
            $descs[$i-1] = isset($_REQUEST[$vdesc]) ? $_REQUEST[$vdesc] : "";
        }

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($box_number) &&
            isset($book_title) &&
            isset($languageId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                $key = $this->generateUniqueKeyValue('books');
                /*$teacherId = DB::table('users')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->where('role_id', 'KWXgk5xtKg')
                    ->value('id');*/

                if($new_book == false){
                    if(
                        !DB::table('books')
                            //->where('teacher_id', $teacherId)
                            ->where('class_id', $classId)
                            ->where('box_number', $box_number)
                            ->update([
                                'book_title' => $book_title,
                                'title1' => $titles[0],
                                'title2' => $titles[1],
                                'title3' => $titles[2],
                                'title4' => $titles[3],
                                'title5' => $titles[4],
                                'title6' => $titles[5],
                                'desc1' => $descs[0],
                                'desc2' => $descs[1],
                                'desc3' => $descs[2],
                                'desc4' => $descs[3],
                                'desc5' => $descs[4],
                                'desc6' => $descs[5]
                            ])
                    ){
                        echo json_encode(array('success' => '0'));
                        die();
                    }else{
                        echo json_encode(array('success' => '1'));
                    }
                }else{
                    if(
                        DB::table('books')
                            ->insert([
                                'id' => $key,
                                //'teacher_id' => $teacherId,
                                'box_number' => $box_number,
                                'book_title' => $book_title,
                                'language_id' => $languageId,
                                'title1' => $titles[0],
                                'title2' => $titles[1],
                                'title3' => $titles[2],
                                'title4' => $titles[3],
                                'title5' => $titles[4],
                                'title6' => $titles[5],
                                'desc1' => $descs[0],
                                'desc2' => $descs[1],
                                'desc3' => $descs[2],
                                'desc4' => $descs[3],
                                'desc5' => $descs[4],
                                'desc6' => $descs[5],
                                'class_id' =>$classId
                            ])
                    ){
                        $this->resolveBookDependencies($key, $classId);
                        echo json_encode(array("success" => "1"));
                    }else{
                        echo json_encode(array("success" => "0"));
                    }
                }

            }else{
                die("Unknown values2");
            }
        }else{
            die("Unknown values1");
        }
    }

    public function deleteBook(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $boxNumber = $this->sanitize($_REQUEST['number']);
        $language = $this->sanitize($_REQUEST['language']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($language) &&
            isset($boxNumber)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                $languageId = DB::table('languages')
                                    ->where('language', $language)
                                    ->value('id');

                if(
                    DB::table('books')
                        ->where('box_number',  $boxNumber)
                        ->where('language_id', $languageId)
                        ->where('class_id', $classId)
                        ->delete()
                ){
                    echo json_encode(array("success" => "1"));
                }else{
                    echo json_encode(array("success" => "0"));
                }
            }else{
                die("Unknown values2");
            }
        }else{
            die("Unknown values1");
        }
    }

    public function setClassCustomQuestions(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);
        $questionId = $this->sanitize($_REQUEST['questionId']);
        $language = $this->sanitize($_REQUEST['language']);
        $question = $this->sanitize($_REQUEST['question']);
        $difficultyLevel = $this->sanitize($_REQUEST['difficultyLevel']);
        $correctAnswer = $this->sanitize($_REQUEST['correctAnswer']);
        $wrongAnswer1 = $this->sanitize($_REQUEST['wrongAnswer1']);
        $wrongAnswer2 = $this->sanitize($_REQUEST['wrongAnswer2']);
        $wrongAnswer3 = $this->sanitize($_REQUEST['wrongAnswer3']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId) &&
            isset($language) &&
            isset($question) &&
            isset($difficultyLevel) &&
            isset($correctAnswer) &&
            isset($wrongAnswer1) &&
            isset($wrongAnswer2) &&
            isset($wrongAnswer3)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                $teacherId = DB::table('users')
                                    ->where('username', $username)
                                    ->where('password', $password)
                                    ->where('role_id', 'KWXgk5xtKg')
                                    ->value('id');

                if(
                    $questionId !== ''
                ){
                    if (
                        DB::table('class_custom_questions')
                            ->where('id', $questionId)
                            ->update([
                                'difficulty_level' => $difficultyLevel,
                                'question' => $question,
                                'correct_answer' => $correctAnswer,
                                'wrong_answer1' => $wrongAnswer1,
                                'wrong_answer2' => $wrongAnswer2,
                                'wrong_answer3' => $wrongAnswer3,
                            ])
                    ) {
                        echo json_encode(array("success" => "1"));
                    } else {
                        echo json_encode(array("success" => "0"));
                    }
                } else {

                    $key = $this->generateUniqueKeyValue('class_custom_questions');

                    if (
                        DB::table('class_custom_questions')
                            ->insert([
                                'id' => $key,
                                'teacher_id' => $teacherId,
                                'class_id' => $classId,
                                'difficulty_level' => $difficultyLevel,
                                'question' => $question,
                                'correct_answer' => $correctAnswer,
                                'wrong_answer1' => $wrongAnswer1,
                                'wrong_answer2' => $wrongAnswer2,
                                'wrong_answer3' => $wrongAnswer3,
                            ])
                    ) {
                        echo json_encode(array("success" => "1", "questionId" => $key));
                    } else {
                        echo json_encode(array("success" => "0"));
                    }

                }

            }else{
                die("Unknown values2");
            }
        }else{
            die("Unknown values1");
        }
    }

    public function deleteClassCustomQuestions(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $questionId = $this->sanitize($_REQUEST['questionId']);

        if(
            isset($username) &&
            isset($password) &&
            isset($questionId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                if(
                    DB::table('class_custom_questions')
                        ->where('id', $questionId)
                        ->delete()
                ){
                    echo json_encode(array("success" => "1"));
                }else{
                    echo json_encode(array("success" => "0"));
                }

            }else{
                die("Unknown values2");
            }
        }else{
            die("Unknown values1");
        }
    }

    public function setCustomNote(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $noteId = $this->sanitize($_REQUEST['noteId']);
        $isStandard = $this->sanitize($_REQUEST['isStandard']);
        $classId = $this->sanitize($_REQUEST['classId']);

        $title = isset($_REQUEST['title']) ? $this->sanitize($_REQUEST['title']) : "";
        $description = isset($_REQUEST['textDescription']) ? $this->sanitize($_REQUEST['textDescription']) : "";

        if(
            isset($username) &&
            isset($password) &&
            isset($noteId) &&
            isset($isStandard)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {

                // update custom note

                $museum_note = DB::table('museum_notes')
                    ->where('class_id', $classId)
                    ->first();

                if(
                    $title != $museum_note->title ||
                    $description != $museum_note->text_description
                ){
                    if(
                        !DB::table('museum_notes')
                            ->where('id', $museum_note->id)
                            ->update([
                                'title' => $title,
                                'text_description' => $description
                            ])
                    ){
                        echo json_encode(array('success' => '0'));
                        die();
                    }
                }

                if(
                    !DB::table('classes')
                        ->where('id', $classId)
                        ->update([
                            'museum_note_id' => $noteId,
                            'is_default' => $isStandard
                        ])
                ){
                    echo json_encode(array('success' => '0'));
                    die();
                }

                echo json_encode(array("success" => "1"));
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

    public function setTeacherClassId(){
        $username = $this->parseSHA1($this->sanitize($_REQUEST['username']));
        $password = $this->parseSHA1($this->sanitize($_REQUEST['password']));
        $classId = $this->sanitize($_REQUEST['classId']);

        if(
            isset($username) &&
            isset($password) &&
            isset($classId)
        ) {
            if ($this->checkTeacherIfExists($username, $password)) {
                if(
                    !DB::table('users')
                        ->where('username', $username)
                        ->where('password', $password)
                        ->where('role_id', 'KWXgk5xtKg')
                        ->update([
                            'class_id' => $classId
                        ])
                ){
                    echo json_encode(array("success" => "0"));
                    die();
                }else{
                    echo json_encode(array("success" => "1"));
                }
            }else{
                die("Unknown values");
            }
        }else{
            die("Unknown values");
        }
    }

};

