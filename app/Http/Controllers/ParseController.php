<?php

namespace App\Http\Controllers;

set_time_limit(12000000);

use Illuminate\Support\Facades\DB;

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

    private function populateAssetType(){
        if(
            !DB::table('asset_type')
                ->insert([
                    [
                        'id' => 1,
                        'type' => 'asset'
                    ],[
                        'id' => 2,
                        'type' => 'sound'
                    ],[
                        'id' => 3,
                        'type' => 'image'
                    ]
                ])
        ){
            die('Cannot populate asset_type table');
        }
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

    private function populateBooks(){
        $books = file_get_contents($this->basepath . 'Book.json');
        $books = json_decode($books)->results;

        $preparedRows = array();

        foreach($books as $book){

            if(
                !isset($book->msclass->objectId) ||
                !isset($book->number) ||
                !isset($book->objectId)
            ){
                continue;
            }

            $bookAllowed = $this->getBookIsAllowed($book->objectId, $book->msclass->objectId);

            $preparedRows[] = array(
                'id' => $book->objectId,
                'teacher_id' => null,
                'class_id' => $book->msclass->objectId,
                'box_number' => $book->number,
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
        }
    }

    private function populateCharacter(){
        if(
            !DB::table('character')
                ->insert([
                    [
                        'id' => 'ihnxqTvlMp',
                        'character' => 'Funky'
                    ],[
                        'id' => 'S0xrM3z67k',
                        'character' => 'Philia'
                    ]
                ])
        ){
            die('Cannot populate character table');
        }
    }

    private function populateClassCustomQuestions(){
        $questions = file_get_contents($this->basepath . 'Question.json');
        $questions = json_decode($questions)->results;

        $preparedRows = array();

        foreach($questions as $question){
            if(
                !isset($question->isCustom)
            ){
                continue;
            }

            if(
                $question->isCustom == false ||
                $question->isCustom == ''
            ){
                continue;
            }

            $level = intval($question->level);

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_custom_questions'),
                'teacher_id' => isset($question->msuser->objectId) ? $question->msuser->objectId : null,
                'class_id' => $question->msclass->objectId,
                'difficulty_level' => isset($level) ? $level : 1,
                'question' => $question->additionalText,
                'correct_answer' => $question->correctAnswerString,
                'wrong_answer1' => $question->wrongAnswerString1,
                'wrong_answer2' => $question->wrongAnswerString2,
                'wrong_answer3' => $question->wrongAnswerString3
            );
        }

        if(
            !DB::table('class_custom_questions')
                ->insert($preparedRows)
        ){
            die("Cannot populate class_custom_questions table");
        }
    }

    private function populateCinema(){
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
                'id' => $cinema->objectId,
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

    private function populateGames(){
        $games = file_get_contents($this->basepath . 'MSGame.json');
        $games = json_decode($games)->results;

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
        }
    }

    private function populateGameSessions(){
        $logs = file_get_contents($this->basepath . 'Log.json');
        $logs = json_decode($logs)->results;

        $preparedRows = array();

        foreach($logs as $log){
            if(
                !isset($log->objectId) ||
                !isset($log->msuser->objectId) ||
                !isset($log->msgame->objectId) ||
                !isset($log->startDatetime->iso) ||
                !isset($log->endDatetime->iso)
            ){
                continue;
            }

            $preparedRows[] = array(
                'id' => $log->objectId,
                'session_startDatetime' => date('Y-m-d H:i:s', strtotime($log->startDatetime->iso)),
                'session_endDatetime' => date('Y-m-d H:i:s', strtotime($log->endDatetime->iso)),
                'user_id' => $log->msuser->objectId,
                'game_id' => $log->msgame->objectId,
                'score' => isset($log->score) ? $log->score : 0,
                'difficulty_level' => isset($log->difficultyLevel) ? $log->difficultyLevel : 0,
                'coins' => 0
            );
        }

        if(
            !DB::table('game_sessions')
                ->insert($preparedRows)
        ){
            die("Cannot populate game_sessions table");
        }
    }

    private function populateShopInventory(){
        $instruments = file_get_contents($this->basepath . 'Instrument.json');
        $instruments = json_decode($instruments)->results;

        $preparedRows = array();
        $preparedRows2 = array();

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
                'instrument_id' => $instrument->objectId,
                'price' => $instrument->price
            );

            $preparedRows2[] = array(
                'id' => $this->generateUniqueKeyValue('instrument_assets'),
                'instrument_id' => $instrument->objectId,
                'title' => $instrument->titleEn,
                'text' => $instrument->textDescriptionEn,
                'language_id' => 1
            );

            $preparedRows2[] = array(
                'id' => $this->generateUniqueKeyValue('instrument_assets'),
                'instrument_id' => $instrument->objectId,
                'title' => $instrument->title,
                'text' => $instrument->textDescription,
                'language_id' => 2
            );

        }

        if(
            !DB::table('shop_inventory')
                ->insert($preparedRows)
        ){
            die("Cannot populate shop_inventory table");
        }

        if(
            !DB::table('instrument_assets')
                ->insert($preparedRows2)
        ){
            die("Cannot populate instrument_assets table");
        }
    }

    private function populateLanguages(){
        if(
            !DB::table('languages')
                ->insert([
                    [
                        'id' => 1,
                        'language' => 'en'
                    ],[
                        'id' => 2,
                        'language' => 'da-DK'
                    ]
                ])
        ){
            die("Cannot populate languages table");
        }
    }

    private function getAsset($id){
        $assets = file_get_contents($this->basepath . 'Asset.json');
        $assets = json_decode($assets)->results;

        foreach($assets as $asset){
            if(
                !isset($asset->objectId)
            ){
                continue;
            }

            if($asset->objectId == $id){
                $name = explode('/', $asset->file->name);
                $name = 'assets/' . $name[count($name) - 1];
                $url = explode('/', $asset->file->url);
                $url = 'assets/' . $url[count($url) - 1];

                return array('name' => $name, 'url' => $url);
            }
        }
    }

    private function populateMuseumAssets(){
        $museum_assets = file_get_contents($this->basepath . 'MuseumAsset.json');
        $museum_assets = json_decode($museum_assets)->results;

        $museum_sub_assets = file_get_contents($this->basepath . 'MuseumSubAsset.json');
        $museum_sub_assets = json_decode($museum_sub_assets)->results;

        $preparedRows = array();
        $preparedRows2 = array();

        foreach($museum_assets as $museum_asset){
            if(
                !isset($museum_asset->objectId) ||
                !isset($museum_asset->name) ||
                !isset($museum_asset->placementId)
            ){
                continue;
            }

            $preparedRows[] = array(
                'id' => $museum_asset->objectId,
                'name' => $museum_asset->name,
                'placement' => $museum_asset->placementId
            );

            foreach($museum_sub_assets as $museum_sub_asset){

                if(
                    !isset($museum_sub_asset->museumAsset->objectId) ||
                    !isset($museum_sub_asset->boxNumber)
                ){
                    continue;
                }

                if($museum_asset->objectId == $museum_sub_asset->museumAsset->objectId){

                    $sound1 = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetSound1->objectId)
                    ){
                        $sound1 = $this->getAsset($museum_sub_asset->assetSound1->objectId);
                    }

                    $sound2 = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetSound2->objectId)
                    ){
                        $sound2 = $this->getAsset($museum_sub_asset->assetSound2->objectId);
                    }

                    $image = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetImage->objectId)
                    ){
                        $image = $this->getAsset($museum_sub_asset->assetImage->objectId);
                    }

                    $preparedRows2[] = array(
                        'id' => $this->generateUniqueKeyValue('museum_sub_assets'),
                        'title' => isset($museum_sub_asset->title) ? $museum_sub_asset->title : "",
                        'subtitle' => isset($museum_sub_asset->subtitle) ? $museum_sub_asset->subtitle : "",
                        'text' => isset($museum_sub_asset->textDescription) ? $museum_sub_asset->textDescription : "",
                        'language_id' => 2,
                        'destinatar_id' => $museum_asset->objectId,
                        'box_number' => $museum_sub_asset->boxNumber,
                        'sound1_name' => isset($museum_sub_asset->assetSound1Title) ? $museum_sub_asset->assetSound1Title : "",
                        'sound2_name' => isset($museum_sub_asset->assetSound2Title) ? $museum_sub_asset->assetSound2Title : "",
                        'sound1_url' => $sound1['url'],
                        'sound2_url' => $sound2['url'],
                        'image' => $image['url']
                    );

                    $sound1 = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetSound1->objectId)
                    ){
                        $sound1 = $this->getAsset($museum_sub_asset->assetSound1->objectId);
                    }

                    $sound2 = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetSound2->objectId)
                    ){
                        $sound2 = $this->getAsset($museum_sub_asset->assetSound2->objectId);
                    }

                    $image = array('name' => null, 'url' => null);

                    if(
                        isset($museum_sub_asset->assetImage->objectId)
                    ){
                        $image = $this->getAsset($museum_sub_asset->assetImage->objectId);
                    }

                    $preparedRows2[] = array(
                        'id' => $this->generateUniqueKeyValue('museum_sub_assets'),
                        'title' => isset($museum_sub_asset->titleEn) ? $museum_sub_asset->titleEn : "",
                        'subtitle' => isset($museum_sub_asset->subtitleEn) ? $museum_sub_asset->subtitleEn : "",
                        'text' => isset($museum_sub_asset->textDescriptionEn) ? $museum_sub_asset->textDescriptionEn : "",
                        'language_id' => 1,
                        'destinatar_id' => $museum_asset->objectId,
                        'box_number' => $museum_sub_asset->boxNumber,
                        'sound1_name' => isset($museum_sub_asset->assetSound1TitleEn) ? $museum_sub_asset->assetSound1TitleEn : "",
                        'sound2_name' => isset($museum_sub_asset->assetSound2TitleEn) ? $museum_sub_asset->assetSound2TitleEn : "",
                        'sound1_url' => $sound1['url'],
                        'sound2_url' => $sound2['url'],
                        'image' => $image['url']
                    );
                }
            }

        }

        if(
            !DB::table('museum_assets')
                ->insert($preparedRows)
        ){
            die("Cannot populate museum_assets table");
        }

        if(
            !DB::table('museum_sub_assets')
                ->insert($preparedRows2)
        ){
            die("Cannot populate museum_sub_assets table");
        }
    }

    private function populateCustomNotesForMissingClasses(){
        $class_ids = DB::table('museum_notes')
            ->whereNotNull('class_id')
            ->lists('class_id');

        $classes = DB::table('classes')
            ->get();

        $preparedRows = array();

        foreach($classes as $class){
            if(
                !in_array($class->id, $class_ids)
            ){
                $preparedRows[] = array(
                    'id' => $this->generateUniqueKeyValue('museum_notes'),
                    'class_id' => $class->id,
                    'title' => '',
                    'text_description' => '',
                    'language_id' => null,
                    'is_default' => false,
                    'audiofile_name' => '',
                    'audiofile_url' => ''
                );
            }
        }

        if(
            !DB::table('museum_notes')
                ->insert($preparedRows)
        ){
            die("Cannot populate museum_notes table");
        }

    }

    private function parseAssetPath($path){
        $path = explode('/', $path);
        $path = 'assets/' . $path[count($path) - 1];

        return $path;
    }

    private function populateMuseumNotes(){
        $notes = file_get_contents($this->basepath . 'MuseumCustomNote.json');
        $notes = json_decode($notes)->results;

        $preparedRows = array();

        foreach($notes as $note){

            if(
                !isset($note->objectId)
            ){
                continue;
            }

            $isStandard = false;

            if(isset($note->isStandard)){
                if($note->isStandard == true){
                    $isStandard = true;
                }else{
                    if(
                        !isset($note->msclass->objectId)
                    ){
                        continue;
                    }
                }
            }

            if($isStandard) {

                $preparedRows[] = array(
                    'id' => $note->objectId,
                    'class_id' => null,
                    'title' => isset($note->title) ? $note->title : "",
                    'text_description' => isset($note->textDescription) ? $note->textDescription : "",
                    'language_id' => 2,
                    'is_default' => 1,
                    'audiofile_name' => isset($note->audioFile->name) ? $this->parseAssetPath($note->audioFile->name) : "",
                    'audiofile_url' => isset($note->audioFile->url) ? $this->parseAssetPath($note->audioFile->url) : ""
                );

                $preparedRows[] = array(
                    'id' => $this->generateUniqueKeyValue('museum_notes'),
                    'class_id' => null,
                    'title' => isset($note->titleEn) ? $note->titleEn : "",
                    'text_description' => isset($note->textDescriptionEn) ? $note->textDescriptionEn : "",
                    'language_id' => 1,
                    'is_default' => 1,
                    'audiofile_name' => isset($note->audioFileEn->name) ? $this->parseAssetPath($note->audioFileEn->name) : "",
                    'audiofile_url' => isset($note->audioFileEn->url) ? $this->parseAssetPath($note->audioFileEn->url) : ""
                );

            }else{
                $preparedRows[] = array(
                    'id' => $this->generateUniqueKeyValue('museum_notes'),
                    'class_id' => $note->msclass->objectId,
                    'title' => isset($note->titleEn) ? $note->titleEn : "",
                    'text_description' => isset($note->textDescriptionEn) ? $note->textDescriptionEn : "",
                    'language_id' => null,
                    'is_default' => 0,
                    'audiofile_name' => "",
                    'audiofile_url' => ""
                );
            }
        }

        if(
            !DB::table('museum_notes')
                ->insert($preparedRows)
        ){
            die('Cannot populate museum_notes table');
        }

        $this->populateCustomNotesForMissingClasses();

    }

    private function populateQuestions(){
        $questions = file_get_contents($this->basepath . 'Question.json');
        $questions = json_decode($questions)->results;

        $preparedRows = array();

        foreach($questions as $question){
            if(
                !isset($question->level) ||
                !isset($question->additionalText) ||
                !isset($question->lang) ||
                !isset($question->type)
            ){
                continue;
            }

            $questionAsset = array('name' => null, 'url' => null);

            if(
                isset($question->additionalAsset->objectId)
            ){
                $questionAsset = $this->getAsset($question->additionalAsset->objectId);
            }

            $correctAnswerString = "";

            if(
                isset($question->correctAnswerString)
            ){
                $correctAnswerString = $question->correctAnswerString;
            }

            $correctAnswerAsset = array('name' => null, 'url' => null);

            if(
                isset($question->correctAnswerAsset->objectId)
            ){
                $correctAnswerAsset = $this->getAsset($question->correctAnswerAsset->objectId);
            }

            $wrongAnswerString1 = "";

            if(
            isset($question->wrongAnswerString1)
            ){
                $wrongAnswerString1 = $question->wrongAnswerString1;
            }

            $wrongAnswer1Asset = array('name' => null, 'url' => null);

            if(
                isset($question->wrongAnswerAsset1->objectId)
            ){
                $wrongAnswer1Asset = $this->getAsset($question->wrongAnswerAsset1->objectId);
            }

            $wrongAnswerString2 = "";

            if(
            isset($question->wrongAnswerString2)
            ){
                $wrongAnswerString2 = $question->wrongAnswerString2;
            }

            $wrongAnswer2Asset = array('name' => null, 'url' => null);

            if(
                isset($question->wrongAnswerAsset2->objectId)
            ){
                $wrongAnswer2Asset = $this->getAsset($question->wrongAnswerAsset2->objectId);
            }

            $wrongAnswerString3 = "";

            if(
            isset($question->wrongAnswerString3)
            ){
                $wrongAnswerString3 = $question->wrongAnswerString3;
            }

            $wrongAnswer3Asset = array('name' => null, 'url' => null);

            if(
                isset($question->wrongAnswerAsset3->objectId)
            ){
                $wrongAnswer3Asset = $this->getAsset($question->wrongAnswerAsset3->objectId);
            }

            if($question->lang == 'En'){
                $language_id = 1;
            }else if($question->lang == 'Da'){
                $language_id = 2;
            }

            $preparedRows[] = array(
                'id' => $question->objectId,
                'difficulty_level' => $question->level,
                'question' => $question->additionalText,
                'question_asset' => $questionAsset['url'],
                'correctAnswer' => $correctAnswerString,
                'correctAnswerAsset' => $correctAnswerAsset['url'],
                'wrongAnswer1' => $wrongAnswerString1,
                'wrongAnswer1Asset' => $wrongAnswer1Asset['url'],
                'wrongAnswer2' => $wrongAnswerString2,
                'wrongAnswer2Asset' => $wrongAnswer2Asset['url'],
                'wrongAnswer3' => $wrongAnswerString3,
                'wrongAnswer3Asset' => $wrongAnswer3Asset['url'],
                'language_id' => $language_id,
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

    private function populateRole(){
        if(
            !DB::table('role')
                ->insert([
                    [
                        'id' => '7PG6XCRphk',
                        'role' => 'Pupil'
                    ],[
                        'id' => 'KWXgk5xtKg',
                        'role' => 'Teacher'
                    ]
                ])
        ){
            die("Cannot populate role table");
        }
    }

    private function populateSchools(){
        $schools = file_get_contents($this->basepath . 'MSSchool.json');
        $schools = json_decode($schools)->results;

        $preparedRows = array();

        foreach($schools as $school){

            if(
                !isset($school->institutionNr) ||
                !isset($school->title)
            ){
                continue;
            }

            if(
                $school->institutionNr == ''
            ){
                continue;
            }

            $access_given = false;

            if(isset($school->accessGiven)){
                if($school->accessGiven == true){
                    $access_given = true;
                }
            }

            $preparedRows[] = array(
                'id' => $school->objectId,
                'institution_nr' => $school->institutionNr,
                'title' => $school->title,
                'access_given' => $access_given
            );
        }

        if(
            !DB::table('schools')
                ->insert($preparedRows)
        ){
            die("Cannot update schools table");
        }
    }

    public function populateTables(){
        $start = time();

        /* Standard Tables
         * asset_type
         * books
         * character
         * class_custom_questions
         * cinema
         * games
         * game_sessions
         * shop_inventory
         * instrument_assets
         * langauges
         * museum_assets
         * museum_sub_assets
         * museum_notes
         * questions
         * role
         * schools
         */

        $this->populateAssetType();
        $this->populateBooks();
        $this->populateCharacter();
        $this->populateClassCustomQuestions();
        $this->populateCinema();
        $this->populateGames();
        $this->populateGameSessions();
        $this->populateShopInventory();
        $this->populateLanguages();
        $this->populateMuseumAssets();
        $this->populateMuseumNotes();
        $this->populateQuestions();
        $this->populateRole();
        $this->populateSchools();

        $end = time();

        echo "Standard tables was populated successfully in " . intval($end - $start) . " seconds<br/>";

    }

    //-------------------------------------

    private function getClassFlags($classId){
        $restrictions = file_get_contents($this->basepath . 'QuestionRestriction.json');
        $restrictions = json_decode($restrictions)->results;

        foreach($restrictions as $restriction){
            if(
                !isset($restriction->msclass->objectId) ||
                !isset($restriction->useAuthorsQuestions) ||
                !isset($restriction->useDefaultQuestions)
            ){
                continue;
            }

            if($restriction->msclass->objectId == $classId){
                return array(
                      'default_questions' => $restriction->useDefaultQuestions,
                      'custom_questions' => $restriction->useAuthorsQuestions
                    );
            }
        }

        return array(
                'default_questions' => null,
                'custom_questions' => null
            );

    }

    private function getClassNote($classId){
        $customNotes = file_get_contents($this->basepath . 'MuseumCustomNote.json');
        $customNotes = json_decode($customNotes)->results;

        foreach($customNotes as $customNote){
            if(
                !isset($customNote->msclass->objectId) ||
                !isset($customNote->textDescription) ||
                !isset($customNote->textDescriptionEn)
            ){
                continue;
            }



            if($classId == $customNote->msclass->objectId){
                return array(
                        'note_id' => $customNote->objectId,
                        'is_default' => false
                    );
            }

            return array(
                    'note_id' => 'zzQ4OP3Xvk',
                    'is_default' => true
                );
        }
    }

    public function classes(){
        $start = time();

        $classes = file_get_contents($this->basepath . 'MSClass.json');
        $classes = json_decode($classes)->results;

        $preparedRows = array();

        foreach($classes as $class){
            if(
                !isset($class->objectId) ||
                !isset($class->title) ||
                !isset($class->groupId) ||
                !isset($class->msschool->objectId)
            ){
                continue;
            }

            $is_active = false;

            if(isset($class->isActive)){
                if($class->isActive == true){
                    $is_active = true;
                }
            }

            $flags = $this->getClassFlags($class->objectId);
            $note = $this->getClassNote($class->objectId);

            $preparedRows[] = array(
                'id' => $class->objectId,
                'name' => $class->title,
                'group' => $class->groupId,
                'use_default_questions' => $flags['default_questions'],
                'use_custom_questions' => $flags['custom_questions'],
                'school_id' => $class->msschool->objectId,
                'museum_note_id' => $note['note_id'],
                'is_default' => $note['is_default'],
                'is_active' => $is_active
            );

        }

        if(
            !DB::table('classes')
                ->insert($preparedRows)
        ){
            die("Cannot populate classes table");
        }

        $end = time();

        echo "Table classes was populated successfully in " . intval($end - $start) . " seconds<br/>";
    }

    private function updateClassGames(){
        DB::statement('drop table if exists class_games_access');
        DB::statement('create table class_games_access as (
          select
            classes.id as class_id,
            games.game_id as game_id,
            true as is_allowed,
            now() as allowed_startDatetime,
            now() as allowed_endDatetime
        from classes join games
        )');
        DB::statement('ALTER TABLE `class_games_access` ADD `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }

    private function updateClassGamesAccess(){

        $class_games = file_get_contents($this->basepath . 'GameRestriction.json');
        $class_games = json_decode($class_games)->results;

        foreach($class_games as $class_game){

            if(
                !isset($class_game->msclass->objectId)||
                !isset($class_game->msgame->objectId)||
                !isset($class_game->endDatetime->iso)
            ){
                continue;
            }

            if(
            !DB::table('class_games_access')
                ->where('class_id', $class_game->msclass->objectId)
                ->where('game_id', $class_game->msgame->objectId)
                ->update([
                    'is_allowed' => false,
                    'allowed_startDatetime' => date("Y-m-d H:i:s", time()),
                    'allowed_endDatetime' => date("Y-m-d H:i:s", strtotime($class_game->endDatetime->iso)),
                ])
            ){
                var_dump($class_game);
                die("Cannot update class_games_access table");
            }

        }
    }

    private function updateClassBooksAccess(){
        $class_books = file_get_contents($this->basepath . 'BookRestriction.json');
        $class_books = json_decode($class_books)->results;

        foreach($class_books as $class_book){

            if(
                !isset($class_book->msclass->objectId) ||
                !isset($class_book->book->objectId) ||
                !isset($class_book->expireDatetime->iso)
            ){
                continue;
            }

            if(
                !DB::table('books')
                    ->where('id', $class_book->book->objectId)
                    ->where('class_id', $class_book->msclass->objectId)
                    ->update([
                        'is_allowed' => false,
                        'allowed_startDatetime' => date("Y-m-d H:i:s", time()),
                        'allowed_endDatetime' => date("Y-m-d H:i:s", strtotime($class_book->expireDatetime->iso))
                    ])
            ){
                /*var_dump($class_book);
                echo("Cannot update books table");*/
                continue;
            }
        }
    }

    private function updateClassInstrumentsAccess(){
        $class_instruments = file_get_contents($this->basepath . 'UserInstruments.json');
        $class_instruments = json_decode($class_instruments)->results;

        $preparedRows = array();

        foreach($class_instruments as $class_instrument){
            if(
                !isset($class_instrument->instrument->objectId) ||
                !isset($class_instrument->msclass->objectId)
            ){
                continue;
            }

            $is_allowed = 0;

            if(
                isset($class_instrument->is_allowed) &&
                $class_instrument -> is_allowed === true
            ){
                $is_allowed = true;
            }

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_instruments_access'),
                'class_id' => $class_instrument->msclass->objectId,
                'instrument' => $class_instrument->instrument->objectId,
                'is_allowed' => $is_allowed,
                'allowed_startDatetime' => time(),
                'allowed_endDatetime' => time()
            );
        }
    }

    /*public function urgentlyUpdateClassInstruments(){
        $instruments = file_get_contents($this->basepath . 'UserInstruments.json');
        $instruments = json_decode($instruments)->results;

        foreach($instruments as $instrument){

            if(
                !isset($instrument->instrument->objectId) ||
                !isset($instrument->msclass->objectId)
            ){
                continue;
            }

            $isAllowed = isset($instrument->isAllowed) ? $instrument->isAllowed : null;

            if($isAllowed != true){
                unset($isAllowed);
            }

            $expireDatetime = isset($instrument->expireDatetime->iso) ? strtotime($instrument->expireDatetime->iso) : 0;

            $user_instrument = DB::table('class_instruments_access')
                ->where('class_id', $instrument->msclass->objectId)
                ->where('instrument_id', $instrument->instrument->objectId)
                ->first();

            if($user_instrument){

                if(
                !DB::table('class_instruments_access')
                    ->where('id', $user_instrument->id)
                    ->update([
                        'is_allowed' => isset($isAllowed) ? true : false,
                        'allowed_startDatetime' => date("Y-m-d H:i:s", time()),
                        'allowed_endDatetime' => date("Y-m-d H:i:s", $expireDatetime)
                    ])
                ){
                    die("Cannot update user_instruments table");
                }

            }

        }
    }*/

    public function updateClassRelations(){
        $start = time();

        $this->updateClassGames();
        $this->updateClassGamesAccess();
        $this->updateClassBooksAccess();
        $this->updateClassInstrumentsAccess();

        $end = time();

        echo "Class dependencies was populated successfully in " . intval($end - $start) . " seconds<br/>";

    }

    //-------------------------------------

    public function users(){
        $start = time();

        $users = file_get_contents($this->basepath . 'MSUser.json');
        $users = json_decode($users)->results;

        $preparedRows = array();
        $i = 0;

        foreach($users as $user){
            echo $i++ . "<br/>";

            if(
                !isset($user->msclass->objectId) ||
                !isset($user->name) ||
                !isset($user->objectId) ||
                !isset($user->role->objectId) ||
                (
                    !isset($user->uniLoginUsername) &&
                    !isset($user->username)
                )
            ){
                continue;
            }

            $password = null;

            if(
                !empty($username)
            ){
                $password = !empty($user->password) ? $user->password : null;
            }

            if($user->role->objectId == 'KWXgk5xtKg'){
                // teacher
                $preparedRows2[] = array(
                    'id' => $this->generateUniqueKeyValue('teacher_classes'),
                    'class_id' => $user->msclass->objectId,
                    'teacher_id' => $user->objectId
                );
            }

            $preparedRows[] = array(
                'id' => $user->objectId,
                'name' => $user->name,
                'username' => !empty($user->uniLoginUsername) ? $user->uniLoginUsername : $user->username,
                'password' => $password,
                'class_id' => $user->msclass->objectId,
                'coins' => isset($user->coins) ? $user->coins : 0,
                'founded_notes' => 0,
                'role_id' => $user->role->objectId,
                'character_id' => isset($user->mscharacter->objectId) ? $user->mscharacter->objectId : null,
                'hall_stars' => isset($user->hallStars) ? intval($user->hallStars) : 0
            );

            if($i % 3000 == 0){
                if(
                    !DB::table('users')
                        ->insert($preparedRows)
                ){
                    die("Cannot populate users table");
                }

                unset($preparedRows);
            }

        }

        if(
        !DB::table('users')
            ->insert($preparedRows)
        ){
            die("Cannot populate users table");
        }

        unset($preparedRows);

        $end = time();

        echo "Table users was populated successfully in " . intval($end - $start) . " seconds<br/>";

    }

    public function teachers(){
        $start = time();

        DB::statement('drop table if exists teacher_classes');
        DB::statement('create table teacher_classes as (select
          users.id as teacher_id,
          c2.id as class_id
        from users
        join classes c1 on users.class_id = c1.id
        join classes c2 on c1.school_id = c2.school_id
        where role_id = \'KWXgk5xtKg\')');
        DB::statement('ALTER TABLE `teacher_classes` ADD `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        $end = time();

        echo "Table teacher_classes was populated successfully in " . intval($end - $start) . " seconds<br/>";
    }

    private function updateUserGamesAccess(){
        $user_games = file_get_contents($this->basepath . 'GameRestriction.json');
        $user_games = json_decode($user_games)->results;

        foreach($user_games as $user_game){

            if(
                !isset($user_game->msuser->objectId)||
                !isset($user_game->msgame->objectId)||
                !isset($user_game->endDatetime->iso)
            ){
                continue;
            }

            if(
            !DB::table('user_games')
                ->where('user_id', $user_game->msuser->objectId)
                ->where('game_id', $user_game->msgame->objectId)
                ->update([
                    'is_allowed' => false,
                    'allowed_startDatetime' => date("Y-m-d H:i:s", time()),
                    'allowed_endDatetime' => date("Y-m-d H:i:s", strtotime($user_game->endDatetime->iso)),
                ])
            ){
                var_dump($user_game);
                die("Cannot update user_games table");
            }

        }

        $statistics = file_get_contents($this->basepath . 'Statistics.json');
        $statistics = json_decode($statistics)->results;

        foreach($statistics as $statistic){
            if(
                !isset($statistic->msuser->objectId) ||
                !isset($statistic->msgame->objectId)
            ){
                continue;
            }

            var_dump($statistic);

            if(
                !DB::table('user_games')
                    ->where('user_id', $statistic->msuser->objectId)
                    ->where('game_id', $statistic->msgame->objectId)
                    ->update([
                        'difficulty_level' => isset($statistic->difficultyLevel) ? $statistic->difficultyLevel : 1,
                        'score' => isset($statistic->score) ? $statistic->score : 0,
                        'game_played' => isset($statistic->gamesPlayed) ? $statistic->gamesPlayed : 0,
                    ])
            ){
                die("Cannot update user_games_2 table");
            }
        }
    }

    private function updateUserInstrumentsAccess(){
        $instruments = file_get_contents($this->basepath . 'UserInstruments.json');
        $instruments = json_decode($instruments)->results;

        foreach($instruments as $instrument){

            if(
                !isset($instrument->instrument->objectId) ||
                !isset($instrument->msuser->objectId)
            ){
                continue;
            }

            $isAllowed = isset($instrument->isAllowed) ? $instrument->isAllowed : null;

            if($isAllowed != true){
                unset($isAllowed);
            }

            $expireDatetime = isset($instrument->expireDatetime->iso) ? strtotime($instrument->expireDatetime->iso) : 0;

            $user_instrument = DB::table('user_instruments')
                ->where('user_id', $instrument->msuser->objectId)
                ->where('instrument_id', $instrument->instrument->objectId)
                ->first();

            if($user_instrument){

                if(
                    !DB::table('user_instruments')
                        ->where('id', $user_instrument->id)
                        ->update([
                            'is_allowed' => isset($isAllowed) ? true : false,
                            'is_achieved' => false,
                            'allowed_startDatetime' => date("Y-m-d H:i:s", time()),
                            'allowed_endDatetime' => date("Y-m-d H:i:s", $expireDatetime)
                        ])
                ){
                    //die("Cannot update user_instruments table");
                    continue;
                }

            }

        }
    }

    private function updateUserBooks(){
        DB::statement('drop table if exists user_books');
        DB::statement('select
          users.id as user_id,
          books.id as book_id,
          true as is_allowed,
          now() as allowed_startDatetime,
          now() as allowed_endDatetime
        from users join books on users.class_id = books.id');
        DB::statement('alter table user_books add `id` int(10) unsigned not null auto_increment primary key first');
    }

    private function updateUserGames(){
        DB::statement('drop table if exists user_games');
        DB::statement('select
          users.id as user_id,
          games.game_id as game_id,
          0 as difficulty_level,
          0 as score,
          0 as game_played,
          true as is_allowed,
          now() as allowed_startDatetime,
          now() as allowed_endDatetime
        from users join games');
        DB::statement('alter table user_games add `id` int(10) unsigned not null auto_increment primary key first');
    }

    public function urgentlyUpdateUserInstruments(){
        $user_instruments = file_get_contents($this->basepath . 'LastUserInstruments.json');
        $user_instruments = json_decode($user_instruments)->results;

        $i = 0;

        foreach($user_instruments as $user_instrument){
            echo $i++ . "<br/>";

            if(
                !isset($user_instrument->instrument->objectId) ||
                !isset($user_instrument->msuser->objectId)
            ){
                continue;
            }

            $u_i = DB::table('user_instruments')
                ->where('user_id', $user_instrument->msuser->objectId)
                ->where('instrument_id', $user_instrument->instrument->objectId)
                ->first();

            if(empty($u_i)){
                continue;
            }

            if(!isset($user_instrument->isAllowed)){
                if(
                    !DB::table('user_instruments')
                        ->where('user_id', $user_instrument->msuser->objectId)
                        ->where('instrument_id', $user_instrument->instrument->objectId)
                        ->update([
                            'is_allowed' => 0,
                            'is_achieved' => 1,
                            'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                            'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
                        ])
                ){
                    //die("Cannot update user_instruments_table");
                    continue;
                }
            }else if(
                isset($user_instrument->isAllowed) &&
                $user_instrument->isAllowed == true
            ){
                if(
                !DB::table('user_instruments')
                    ->where('user_id', $user_instrument->msuser->objectId)
                    ->where('instrument_id', $user_instrument->instrument->objectId)
                    ->update([
                        'is_allowed' => 0,
                        'is_achieved' => 0,
                        'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                        'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
                    ])
                ){
                    //die("Cannot update user_instruments_table");
                    continue;
                }
            }else if(
                isset($user_instrument->isAllowed) &&
                $user_instrument->isAllowed == false
            ){
                if(
                !DB::table('user_instruments')
                    ->where('user_id', $user_instrument->msuser->objectId)
                    ->where('instrument_id', $user_instrument->instrument->objectId)
                    ->update([
                        'is_allowed' => 0,
                        'is_achieved' => 1,
                        'allowed_startDatetime' => date("Y-m-d H:i:s", 0),
                        'allowed_endDatetime' => date("Y-m-d H:i:s", 0),
                    ])
                ){
                    //die("Cannot update user_instruments_table");
                    continue;
                }
            }

        }
    }

    private function updateUserInstruments(){
        $user_instruments = file_get_contents($this->basepath . 'UserInstruments.json');
        $user_instruments = json_decode($user_instruments)->results;

        $preparedRows = array();

        foreach($user_instruments as $user_instrument){
            if(
                !isset($user_instrument->instrument->objectId) ||
                !isset($user_instrument->msuser->objectId)
            ){
                continue;
            }

            $is_allowed = 0;

            if(
                isset($user_instrument->is_allowed) &&
                $user_instrument -> is_allowed === true
            ){
                $is_allowed = true;
            }

            $preparedRows[] = array(
                'id' => $this->generateUniqueKeyValue('class_instruments_access'),
                'user_id' => $user_instrument->msuser->objectId,
                'instrument' => $user_instrument->instrument->objectId,
                'is_allowed' => $is_allowed,
                //'is_achieved' =>
                'allowed_startDatetime' => time(),
                'allowed_endDatetime' => time()
            );
        }
    }

    private function updateUserRelations(){

        $this->updateUserBooks();
        $this->updateUserGames();
        $this->updateUserInstruments();

        $this->updateUserGamesAccess();
        $this->updateUserInstrumentsAccess();
    }

    // ------------------------------------

    public function populateDatabase(){

        $start = time();

        $this->populateTables();
        $this->classes();
        $this->users();
        $this->teachers();

        $this->updateClassRelations();

        $this->updateUserRelations();

        $end = time();

        echo "Total time: " . intval($end - $start) . "<br/>";

    }

    public function updateUsers(){
        $userIds = array(
            'LC51IKopwl',
            '3Tl5KvscWT',
            'KLFgZ9CzEH',
            '6NQE655ITY',
            'l5Y0qCVaa8',
            'i6KPwaEIc7',
            'zXkfYVfpL8',
            'Rr4nB9goFV',
            'fZAC8vtOs7',
            'WkY8kxyxXl',
        );

        $instruments = DB::table('shop_inventory')
            ->get();

        $games = DB::table('games')
            ->get();

        //$preparedRows = array();

        $i = 0;

        foreach($userIds as $userId) {

            foreach ($instruments as $instrument) {
                echo "insert into user_instruments
                (`id`, `user_id`, `instrument_id`, `is_allowed`, `is_achieved`, `allowed_startDatetime`, `allowed_endDatetime`)
                values
                (null, '" . $userId . "', '" . $instrument->instrument_id . "', 0, false, '" . date("Y-m-d H:i:s", 0) . "', '" . date("Y-m-d H:i:s", 0) . "');<br/>";
            }

            foreach ($games as $game) {
                echo "insert into user_games
                (`id`, `user_id`, `game_id`, `difficulty_level`, `score`, `game_played`, `is_allowed`, `allowed_startDatetime`, `allowed_endDatetime`)
                values
                (null, '" . $userId . "', '" . $game->game_id . "', 1, 0, 0, 1, '" . date("Y-m-d H:i:s", 0) . "', '" . date("Y-m-d H:i:s", 0) . "');<br/>";
            }

        }

        $this->updateClasses();

    }

    private function updateClasses(){
        $classIds = array(
            'pfObjMOEVJ'
        );

        $instruments = DB::table('shop_inventory')
            ->get();

        $games = DB::table('games')
            ->get();

        foreach($classIds as $classId) {

            foreach ($instruments as $instrument) {
                echo "insert into class_instruments_access
                (`id`, `class_id`, `instrument_id`, `is_allowed`, `allowed_startDatetime`, `allowed_endDatetime`)
                values
                (null, '" . $classId . "', '" . $instrument->instrument_id . "', 1, '" . date("Y-m-d H:i:s", 0) . "', '" . date("Y-m-d H:i:s", 0) . "');<br/>";
            }

            foreach ($games as $game) {
                echo "insert into class_games_access
                (`id`, `class_id`, `game_id`, `is_allowed`, `allowed_startDatetime`, `allowed_endDatetime`)
                values
                (null, '" . $classId . "', '" . $game->game_id . "', true, '" . date("Y-m-d H:i:s", 0) . "', '" . date("Y-m-d H:i:s", 0) . "');<br/>";
            }

        }
    }

}
