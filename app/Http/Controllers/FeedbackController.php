<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $default_dpname;

    public function __construct()
    {
        // get default DPName
        $row = DB::table('cases')->select('DPName')
                                ->orderBy('id', 'desc')
                                ->orderBy('CaseSN', 'desc')
                                ->first();
        $this->default_dpname = $row->DPName;
    }

    public function fetchAll()
    {
        $default_dpname = $this->default_dpname;
        $rows = DB::table('feedbacks')->where('DPName', $default_dpname)->get();
        $result = [];
        foreach ($rows as $row) {
            $item = [];
            $item['DPName'] = $row->DPName;
            $item['CaseSN'] = $row->CaseSN;
            $item['description'] = $row->description;
            $item['solve_suggest'] = $row->solve_suggest;
            if ($row->photo_link !== null) {
                $item['photo_link'] = json_decode($row->photo_link);
            } else {
                $item['photo_link'] = [];
            }
            if ($row->video_link !== null) {
                $item['video_link'] = json_decode($row->video_link);
            } else {
                $item['video_link'] = [];
            }
            $item['reporter'] = $row->reporter;

            $result[] = $item;
        }

        return response()->json($result);
    }

    public function fetch(Request $request, $CaseSN)
    {
        $default_dpname = $this->default_dpname;
        $case_sn = intval($CaseSN);
        $rows = DB::table('feedbacks')->where('DPName', $default_dpname)
                                    ->where('CaseSN', $case_sn)
                                    ->get();

        if (!empty($rows)) {
            $result = [];
            foreach ($rows as $row) {
                $item = [];
                $item['DPName'] = $row->DPName;
                $item['CaseSN'] = $row->CaseSN;
                $item['description'] = $row->description;
                $item['solve_suggest'] = $row->solve_suggest;
                if ($row->photo_link !== null) {
                    $item['photo_link'] = json_decode($row->photo_link);
                } else {
                    $item['photo_link'] = [];
                }
                if ($row->video_link !== null) {
                    $item['video_link'] = json_decode($row->video_link);
                } else {
                    $item['video_link'] = [];
                }
                $item['reporter'] = $row->reporter;

                $result[] = $item;
            }
        } else {
            $result = [];
        }

        return response()->json($result);
    }

    public function create(Request $request)
    {
        $validater = $this->validate($request, [
            'CaseSN' => 'required',
            'description' => 'required',
            'solve_suggest' => 'required|boolean'
        ]);

        if ($validater != null) {
            abort(400);
        }

        $fields = ['CaseSN', 'description', 'solve_suggest'];
        $input_data = $request->all();
        if ($request->has('photo_link')) {
            $fields[] = 'photo_link';
            $input_data['photo_link'] = json_encode($input_data['photo_link']);
        }
        if ($request->has('video_link')) {
            $fields[] = 'video_link';
            $input_data['video_link'] = json_encode($input_data['video_link']);
        }
        if ($request->has('reporter')) {
            $fields[] = 'reporter';
            $input_data['reporter'] = json_encode($input_data['reporter']);
        }

        $insert_data = array_filter($input_data, function ($k) use ($fields) {
            return in_array($k, $fields);
        }, ARRAY_FILTER_USE_KEY);
        $insert_data['DPName'] = $this->default_dpname;
        // convert solve_suggest to boolean
        if ($insert_data['solve_suggest']) {
            $insert_data['solve_suggest'] = true;
        } else {
            $insert_data['solve_suggest'] = false;
        }

        // insert data to database
        $result = DB::table('feedbacks')->insert($insert_data);
        if ($result) {
            return response()->json([
                'message' => 'Insert succeed.'
            ]);
        }
    }
}
