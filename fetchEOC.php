<?php

$eoc_data_url = 'https://tcgbusfs.blob.core.windows.net/blobfs/GetDisasterSummary.gz';
$data_gzip = file_get_contents($eoc_data_url);
$raw_data = gzdecode($data_gzip);
$raw_data_array = json_decode($raw_data, true);
$eoc_data_list = $raw_data_array['DataSet']['diffgr:diffgram']['NewDataSet']['CASE_SUMMARY'];
$inserted_count = 0;
$update_count = 0;

// connect db
$db_host = getenv('DB_HOST');
$db_database = getenv('DB_DATABASE');
$dsn = "mysql:dbname={$db_database};host={$db_host};charset=utf8";
$db_user = getenv('DB_USERNAME');
$db_pass = getenv('DB_PASSWORD');
try {
    $dbh = new PDO($dsn, $db_user, $db_pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

foreach ($eoc_data_list as $eoc_data) {
    // check duplicate
    $sql = 'select * from cases where DPName=? and CaseSN=?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($eoc_data['DPName'], intval($eoc_data['CaseSN'])));
    $case_count = $sth->rowCount();

    if ($case_count !== 0) {
        // remove useless data
        unset($eoc_data['-diffgr:id']);
        unset($eoc_data['-msdata:rowOrder']);
        // transform some data to correct type
        $eoc_data['CaseID'] = trim($eoc_data['CaseID']);
        $eoc_data['CaseSN'] = intval($eoc_data['CaseSN']);
        if ($eoc_data['CaseComplete'] === 'false') {
            $eoc_data['CaseComplete'] = 0;
        } else {
            $eoc_data['CaseComplete'] = 1;
        }
        $eoc_data['Wgs84X'] = doubleval($eoc_data['Wgs84X']);
        $eoc_data['Wgs84Y'] = doubleval($eoc_data['Wgs84Y']);
        // update data to database
        $sql = "update cases set DPName=:DPName, CaseID=:CaseID, CaseSN=:CaseSN, CaseTime=:CaseTime,
            Name=:Name, CaseLocationDistrict=:CaseLocationDistrict, CaseLocationDescription=:CaseLocationDescription,
            CaseDescription=:CaseDescription, CaseComplete=:CaseComplete, PName=:PName, Wgs84X=:Wgs84X,
            Wgs84Y=:Wgs84Y, CaseCommunicatorUnit=:CaseCommunicatorUnit, CaseSerious=:CaseSerious where DPName='{$eoc_data['DPName']}' and CaseSN='{$eoc_data['CaseSN']}'";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute($eoc_data);
        if ($result) {
            $update_count++;
        }
    } else {
        // remove useless data
        unset($eoc_data['-diffgr:id']);
        unset($eoc_data['-msdata:rowOrder']);
        // transform some data to correct type
        $eoc_data['CaseID'] = trim($eoc_data['CaseID']);
        $eoc_data['CaseSN'] = intval($eoc_data['CaseSN']);
        if ($eoc_data['CaseComplete'] === 'false') {
            $eoc_data['CaseComplete'] = 0;
        } else {
            $eoc_data['CaseComplete'] = 1;
        }
        $eoc_data['Wgs84X'] = doubleval($eoc_data['Wgs84X']);
        $eoc_data['Wgs84Y'] = doubleval($eoc_data['Wgs84Y']);
        // insert data to database
        $sql = "insert into cases (DPName, CaseID, CaseSN, CaseTime, Name, CaseLocationDistrict,
            CaseLocationDescription, CaseDescription, CaseComplete, PName, Wgs84X, Wgs84Y, CaseCommunicatorUnit, CaseSerious) values (
            :DPName, :CaseID, :CaseSN, :CaseTime, :Name, :CaseLocationDistrict,
            :CaseLocationDescription, :CaseDescription, :CaseComplete, :PName, :Wgs84X, :Wgs84Y, :CaseCommunicatorUnit, :CaseSerious)";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute($eoc_data);
        if ($result) {
            $inserted_count++;
        }
    }
}

echo json_encode([
    'message' => 'Fetch succeed.',
    'inserted_count' => $inserted_count,
    'update_count' => $update_count
]);
