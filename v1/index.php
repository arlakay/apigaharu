<?php

//including the required files
require_once '../include/DbOperation.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();



/* *
 * URL: http://localhost/StudentApp/v1/createassignment
 * Parameters: name, details, facultyid, studentid
 * Method: POST
 * */
$app->post('/createassignment',function() use ($app){
    verifyRequiredParams(array('name','details','facultyid','studentid'));

    $name = $app->request->post('name');
    $details = $app->request->post('details');
    $facultyid = $app->request->post('facultyid');
    $studentid = $app->request->post('studentid');

    $db = new DbOperation();

    $response = array();

    if($db->createAssignment($name,$details,$facultyid,$studentid)){
        $response['error'] = false;
        $response['message'] = "Assignment created successfully";
    }else{
        $response['error'] = true;
        $response['message'] = "Could not create assignment";
    }

    echoResponse(200,$response);

});

/* *
 * URL: http://localhost/StudentApp/v1/assignments/<student_id>
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: GET
 * */
$app->get('/assignments/:id', 'authenticateStudent', function($student_id) use ($app){
    $db = new DbOperation();
    $result = $db->getAssignments($student_id);
    $response = array();
    $response['error'] = false;
    $response['assignments'] = array();
    while($row = $result->fetch_assoc()){
        $temp = array();
        $temp['id']=$row['id'];
        $temp['name'] = $row['name'];
        $temp['details'] = $row['details'];
        $temp['completed'] = $row['completed'];
        $temp['faculty']= $db->getFacultyName($row['faculties_id']);
        array_push($response['assignments'],$temp);
    }
    echoResponse(200,$response);
});


/* *
 * URL: http://localhost/StudentApp/v1/submitassignment/<assignment_id>
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->put('/submitassignment/:id', 'authenticateFaculty', function($assignment_id) use ($app){
    $db = new DbOperation();
    $result = $db->updateAssignment($assignment_id);
    $response = array();
    if($result){
        $response['error'] = false;
        $response['message'] = "Assignment submitted successfully";
    }else{
        $response['error'] = true;
        $response['message'] = "Could not submit assignment";
    }
    echoResponse(200,$response);
});



/* *
 * URL: http://localhost/GAHARU/v1/members
 * Parameters: none
 * Method: GET
 * */
$app->get('/members', function() use ($app){
    $db = new DbOperation();
    $result = $db->getAllMembers();
    $response = array();
    $response['error'] = false;
    $response['member'] = array();

    while($row = $result->fetch_assoc()){
        $temp = array();
        $temp['id'] = $row['id'];
        $temp['name'] = $row['name'];
        $temp['email'] = $row['email'];
		$temp['phone'] = $row['phone'];
		$temp['created_by'] = $row['created_by'];
		$temp['created_on'] = $row['created_on'];
		$temp['updated_by'] = $row['updated_by'];
		$temp['updated_on'] = $row['updated_on'];
		$temp['deleted'] = $row['deleted'];
		
        array_push($response['member'],$temp);
    }
    echoResponse(200,$response);
});

/* *
 * URL: http://localhost/GAHARU/v1/createmember
 * Parameters: name, email, phone, password
 * Method: POST
 * */
$app->post('/createmember', function () use ($app) {
    verifyRequiredParams(array('name', 'email', 'phone'));
    $response = array();
    $name = $app->request->post('name');
    $email = $app->request->post('email');
    $phone = $app->request->post('phone');
    $db = new DbOperation();
    $res = $db->createMember($name, $email, $phone);
    if ($res == 0) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoResponse(201, $response);
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Sorry, this student  already existed";
        echoResponse(200, $response);
    }
});

/* *
 * URL: http://localhost/GAHARU/v1/memberlogin
 * Parameters: email, password
 * Method: POST
 * */
$app->post('/memberlogin', function () use ($app) {
    verifyRequiredParams(array('email', 'password'));
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $db = new DbOperation();
    $response = array();
    if ($db->memberLogin($email, $password)) {
        $member = $db->getMember($email);
        $response['error'] = false;
		
		$response['id'] = $member['id'];
        $response['name'] = $member['name'];
        $response['email'] = $member['email'];
		$response['phone'] = $member['phone'];
		$response['created_by'] = $member['created_by'];
		$response['created_on'] = $member['created_on'];
		$response['updated_by'] = $member['updated_by'];
		$response['updated_on'] = $member['updated_on'];
		$response['deleted'] = $member['deleted'];
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid username or password";
    }
    echoResponse(200, $response);
});

/* *
 * URL: http://localhost/GAHARU/v1/events
 * Parameters: none
 * Method: GET
 * */
$app->get('/events', function() use ($app){
    $db = new DbOperation();
    $result = $db->getAllEvents();
    $response = array();
    $response['error'] = false;
    $response['events'] = array();

    while($row = $result->fetch_assoc()){
        $temp = array();
        $temp['id'] = $row['id'];
        $temp['name'] = $row['name'];
		$temp['venue_name'] = $row['venue_name'];
		$temp['address'] = $row['address'];
		$temp['website'] = $row['website'];
		$temp['user_id'] = $row['user_id'];
		$temp['city_id'] = $row['city_id'];
		$temp['venue_id'] = $row['venue_id'];
		$temp['operation_date'] = $row['operation_date'];
		$temp['operation_time'] = $row['operation_time'];
        $temp['reg_start_date'] = $row['reg_start_date'];
		$temp['reg_end_date'] = $row['reg_end_date'];
		$temp['topic'] = $row['topic'];
		$temp['category'] = $row['category'];
		$temp['target'] = $row['target'];
		$temp['description'] = $row['description'];
		
        array_push($response['events'],$temp);
    }
    echoResponse(200,$response);
});

//--------------------------------------------------------------------BELOM SELESAI----------------------------------------------------------------------------

$app->post('/event_cek_participant', function() use ($app){
	verifyRequiredParams(array('member_id', 'event_id'));
    $mid = $app->request->post('member_id');
    $eid = $app->request->post('event_id');
    $db = new DbOperation();
    $response = array();
    if ($db->cekParticipant($mid, $eid)) {
        $participant = $db->getParticipant($mid, $eid);
        $response['error'] = false;
		
		$response['id'] = $participant['id'];
		$response['event_id'] = $participant['event_id'];
		$response['member_id'] = $participant['member_id'];
        $response['reg_date'] = $participant['reg_date'];
        $response['reg_time'] = $participant['reg_time'];
		$response['attend'] = $participant['attend'];
		$response['created_by'] = $participant['created_by'];
		$response['created_on'] = $participant['created_on'];
		$response['updated_by'] = $participant['updated_by'];
		$response['updated_on'] = $participant['updated_on'];
		$response['deleted'] = $participant['deleted'];
    } else {
        $response['error'] = true;
        $response['message'] = "Participant Belum Terdaftar";
    }
    echoResponse(200, $response);
});

$app->post('/event_register', function() use ($app){
	verifyRequiredParams(array('event_id', 'member_id'));
    $response = array();
    $eid = $app->request->post('event_id');
    $mid = $app->request->post('member_id');
    $db = new DbOperation();
    $res = $db->addParticipant($eid, $mid);
    if ($res == 0) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoResponse(201, $response);
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Sorry, this student  already existed";
        echoResponse(200, $response);
    }
});

$app->put('/event_attendance', function() use ($app){
	verifyRequiredParams(array('member_id','event_id','id'));
    $response = array();

    $mid = $app->request->params('member_id');
	$eid = $app->request->params('event_id');
	$id = $app->request->params('id');
	
	$db = new DbOperation();
    $result = $db->updateAttendance($mid, $eid, $id);
    if($result){
        $response['error'] = false;
        $response['message'] = "Participant updated successfully";
    }else{
        $response['error'] = true;
        $response['message'] = "Could not update participant";
    }
    echoResponse(200,$response);
});

$app->post('/event_feedback', function() use ($app){
	verifyRequiredParams(array('event_participant_id', 'rate', 'useful', 'interesting', 'purpose', 'feed_back', 'sugest', 'product', 'app_url', 'web_id'));
    $response = array();
    $epi = $app->request->post('event_participant_id');
    $rat = $app->request->post('rate');
	$use = $app->request->post('useful');
	$itr = $app->request->post('interesting');
	$pur = $app->request->post('purpose');
	$fdb = $app->request->post('feed_back');
	$sgs = $app->request->post('sugest');
	$pro = $app->request->post('product');
	$arl = $app->request->post('app_url');
	$wrl = $app->request->post('web_id');
	
    $db = new DbOperation();
    $res = $db->saveFeedback($epi, $rat, $use, $itr, $pur, $fdb, $sgs, $pro, $arl, $wrl);
	
    if ($res == 0) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoResponse(201, $response);
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Sorry, this student  already existed";
        echoResponse(200, $response);
    }
});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}


function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

function authenticateStudent(\Slim\Route $route)
{
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    if (isset($headers['Authorization'])) {
        $db = new DbOperation();
        $api_key = $headers['Authorization'];
        if (!$db->isValidStudent($api_key)) {
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}


function authenticateFaculty(\Slim\Route $route)
{
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    if (isset($headers['Authorization'])) {
        $db = new DbOperation();
        $api_key = $headers['Authorization'];
        if (!$db->isValidFaculty($api_key)) {
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

$app->run();