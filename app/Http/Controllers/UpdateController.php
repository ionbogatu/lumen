<?php
/**
 * Created by PhpStorm.
 * User: Otinsoft
 * Date: 12.04.2016
 * Time: 11:03
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class UpdateController extends Controller{

    private $basepath = "C:\\Users\\Otinsoft\\Desktop\\WebGame DB\\ParseData\\";

    public function updateBookRestrictions(){

        $book_restrictions = file_get_contents($this->basepath . 'BookRestriction.json');
        $book_restrictions = json_decode($book_restrictions)->results;

        //var_dump($book_restrictions);

        foreach($book_restrictions as $book_restriction){
            $book = DB::table('books')
                ->where('id', $book_restriction->book->objectId)
                ->where('class_id', $book_restriction->msclass->objectId)
                ->first();

            var_dump($book);
        }

    }

}