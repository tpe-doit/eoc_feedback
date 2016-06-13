<?php

namespace App\Http\Controllers;

use DB;

class CaseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function fetchtest()
    {
        return "fetch test";
    }

    public function fetchEOCdata()
    {
        $eoc_data_url = 'https://tcgbusfs.blob.core.windows.net/blobfs/GetDisasterSummary.gz';
        $data_gzip = file_get_contents($eoc_data_url);
        $raw_data = gzdecode($data_gzip);
        $raw_data_array = json_decode($raw_data, true);
        $eoc_data_list = $raw_data_array['DataSet']['diffgr:diffgram']['NewDataSet']['CASE_SUMMARY'];
        $inserted_count = 0;
        $duplicate_count = 0;
        foreach ($eoc_data_list as $eoc_data) {
            // check duplicate
            $case_count = DB::table('cases')->where('DPName', $eoc_data['DPName'])
                                            ->where('CaseSN', intval($eoc_data['CaseSN']))
                                            ->count();
            if ($case_count !== 0) {
                $duplicate_count++;
            } else {
                // remove useless data
                unset($eoc_data['-diffgr:id']);
                unset($eoc_data['-msdata:rowOrder']);
                // transform some data to correct type
                $eoc_data['CaseSN'] = intval($eoc_data['CaseSN']);
                if ($eoc_data['CaseComplete'] === 'false') {
                    $eoc_data['CaseComplete'] = false;
                } else {
                    $eoc_data['CaseComplete'] = true;
                }
                $eoc_data['Wgs84X'] = doubleval($eoc_data['Wgs84X']);
                $eoc_data['Wgs84Y'] = doubleval($eoc_data['Wgs84Y']);
                // insert data to database
                $result = DB::table('cases')->insert($eoc_data);
                $inserted_count++;
            }
        }

        return response()->json([
            'message' => 'Fetch succeed.',
            'inserted_count' => $inserted_count,
            'duplicate_count' => $duplicate_count
        ], 200);
    }
}
