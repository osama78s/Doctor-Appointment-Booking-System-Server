<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class testController extends Controller
{
    public function store_test(Request $request)
    {
        $locale = app()->getLocale();
        if ($locale == 'ar')
         {
            $test = Test::create(
                [
                    'name'              =>$request->name,
                    'descreption'       => $request->disc,
                    'created_at'        => Carbon::now(),
                    'lang' => $locale,
                    'category_id' 
                ]);
        }
      


            // $test = Test::create(
            //     [
            //         'name'              => ["en" => $request->name_en, "ar" => $request->name_ar],
            //         'descreption'       => ["en" => $request->descreption_en, "ar" => $request->descreption_ar],
            //         'created_at'        => Carbon::now(),
            //     ]);

            return response()->json($test);
    }

    public function index()
    {
        $tests = Test::all();
        // $locale = app()->getLocale();
        
        // // $tests = Test::select('name->'.$locale,'descreption->'.$locale)->get();


        // // $tests = DB::table('tests')
        // //     ->select(DB::raw("json_unquote(json_extract(`name`, '$.\"ar\"')) as arabic_name"))
        // //     ->get();


        // $tests = DB::select("
        // SELECT 
        //     id, 
        //     json_unquote(json_extract(name, '$.$locale')) AS name, 
        //     json_unquote(json_extract(descreption, '$.$locale')) AS description, 
        //     created_at, 
        //     updated_at 
        // FROM tests
        // ");

        
        return response()->json($tests);
    }


    public function get_test($id)
    {
        $locale = app()->getLocale();

$test = DB::select("
        SELECT 
            id, 
            json_unquote(json_extract(name, '$.$locale')) AS name, 
            json_unquote(json_extract(descreption, '$.$locale')) AS description, 
            created_at, 
            updated_at 
        FROM tests
         WHERE id = $id
        ");
        return  response()->json($test);
    }
    
}
