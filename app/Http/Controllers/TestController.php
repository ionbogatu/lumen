<?php
/**
 * Created by PhpStorm.
 * User: Otinsoft
 * Date: 05.04.2016
 * Time: 13:03
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestController extends Controller {

    private $basepath = "C:\\Users\\Otinsoft\\Desktop\\WebGame DB\\ParseData\\";

    public function test(){
        $users = file_get_contents($this->basepath . 'MSUser.json');
        $users = json_decode($users)->results;

        $i = 0;

        foreach($users as $user) {
            if (
                !isset($user->msclass->objectId) ||
                !isset($user->name) ||
                !isset($user->objectId) ||
                !isset($user->role->objectId) ||
                !isset($user->uniLoginUsername)
            ) {
                continue;
            }

            $i++;
        }

        echo $i;
    }
}