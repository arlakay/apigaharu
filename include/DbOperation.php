<?php

class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }
  
    //method to register a new facultly
    public function createFaculty($name,$username,$pass,$subject){
        if (!$this->isFacultyExists($username)) {
            $password = md5($pass);
            $apikey = $this->generateApiKey();
            $stmt = $this->con->prepare("INSERT INTO faculties(name, username, password, subject, api_key) values(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $username, $password, $subject, $apikey);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }

    //method to let a faculty log in
    public function facultyLogin($username, $pass){
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM faculties WHERE username=? and password =?");
        $stmt->bind_param("ss",$username,$password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }

    //Method to create a new assignment
    public function createAssignment($name,$detail,$facultyid,$studentid){
        $stmt = $this->con->prepare("INSERT INTO assignments (name,details,faculties_id,students_id) VALUES (?,?,?,?)");
        $stmt->bind_param("ssii",$name,$detail,$facultyid,$studentid);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            return true;
        }
        return false;
    }

    //Method to update assignment status
    public function updateAssignment($id){
        $stmt = $this->con->prepare("UPDATE assignments SET completed = 1 WHERE id=?");
        $stmt->bind_param("i",$id);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            return true;
        }
        return false;
    }

    //Method to get all the assignments of a particular student
    public function getAssignments($studentid){
        $stmt = $this->con->prepare("SELECT * FROM assignments WHERE students_id=?");
        $stmt->bind_param("i",$studentid);
        $stmt->execute();
        $assignments = $stmt->get_result();
        $stmt->close();
        return $assignments;
    }
	
	//Method to fetch all assignment from database
    public function getAllAssignments(){
        $stmt = $this->con->prepare("SELECT * FROM assignments");
        $stmt->execute();
        $assignments = $stmt->get_result();
        $stmt->close();
        return $assignments;
    }

	//--------------------------------------------------------GAHARU---------------------------------------------------//

	//Method to fetch all Member from database GAHARU
    public function getAllMembers(){
        $stmt = $this->con->prepare("SELECT * FROM member");
        $stmt->execute();
        $members = $stmt->get_result();
        $stmt->close();
        return $members;
    }
	
	//Method to let a Member log in GAHARU
    public function memberLogin($email,$pass){
        //$password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM member WHERE email=? and phone=?");
        $stmt->bind_param("ss",$email,$pass);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }
	
	//Method to get member details GAHARU
    public function getMember($email){
        $stmt = $this->con->prepare("SELECT * FROM member WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $member;
    }
	
	//Method to register a new member GAHARU
    public function createMember($name,$email,$phone){
        if (!$this->isMemberExists($email)) {
            //$password = md5($phone);
            $apikey = $this->generateApiKey();
			$stat_deleted = "0";
            $stmt = $this->con->prepare("INSERT INTO member(name, email, phone, created_on, deleted) values(?, ?, ?, NOW(), ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $stat_deleted);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }
	
	//Method to check the Member email already exist or not GAHARU
    private function isMemberExists($email) {
        $stmt = $this->con->prepare("SELECT id from member WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
	
	//Method to fetch all Member from database GAHARU
    public function getAllEvents(){
        $stmt = $this->con->prepare("SELECT 
									e.id, e.name, e.user_id, e.city_id, e.venue_id, e.operation_date, e.operation_time, 
									e.reg_end_date, e.reg_start_date, e.topic, e.category, e.target, e.description, 
									e.poster, v.name as venue_name, v.address, v.contact_person, v.website, v.vendor_id  
									FROM event e 
									INNER JOIN venues v
									ON e.venue_id = v.id;");
        $stmt->execute();
        $events = $stmt->get_result();
        $stmt->close();
        return $events;
    }
	
	public function cekParticipant($member_id, $event_id){
	    $stmt = $this->con->prepare("SELECT * FROM event_participant 
									WHERE member_id = ? AND event_id = ?");
        $stmt->bind_param("ss", $member_id, $event_id);
        $stmt->execute();
		$stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0; 
	}
	
	public function getParticipant($member_id, $event_id){
	    $stmt = $this->con->prepare("SELECT * FROM event_participant 
									WHERE member_id = ? AND event_id = ?");
        $stmt->bind_param("ss", $member_id, $event_id);
        $stmt->execute();
        $participant = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $participant;
	}
	
	
	public function addParticipant($event_id, $member_id){
		if (!$this->cekParticipant($member_id, $event_id)) {
            //$password = md5($phone);
            $apikey = $this->generateApiKey();
			$attendance = "N";
			$create_by = "apps";
			$stat_deleted = "0";
			$stmt = $this->con->prepare("INSERT INTO event_participant
										(event_id, member_id, reg_date, reg_time, attend, created_by, created_on, deleted)
										VALUES(?,?,curdate(),now(),?,?,curdate(),?)");            
			$stmt->bind_param("sssss", $event_id, $member_id, $attendance, $create_by, $stat_deleted);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
	}
	
	public function updateAttendance($member_id, $event_id, $id){
		$attendance = "Y";
		$update_by = "apps";
		$stmt = $this->con->prepare("UPDATE event_participant
									SET attend = ?, updated_by = ?, updated_on = curdate()
									WHERE member_id = ? 
									AND event_id = ? 
									AND id = ?");
		$stmt->bind_param("ssiii",$attendance, $update_by, $member_id, $event_id, $id);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            return true;
        }
        return false;
	}
	
	public function cekFeedback($ev_participant_id){
		$stmt = $this->con->prepare("SELECT * FROM event_feedback 
									WHERE event_participant_id = ? ");
        $stmt->bind_param("s", $ev_participant_id);
        $stmt->execute();
        $feedback = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $feedback;
	}
	
	public function saveFeedback($ev_particpant_id, $rate, $useful, $interesting, $purpose, $feedback, $suggest, $product, $appurl, $webid){
		if (!$this->cekFeedback($ev_particpant_id)) {
            //$password = md5($phone);
            //$apikey = $this->generateApiKey();
			$create_by = "apps";
			$stat_deleted = "0";
			$stmt = $this->con->prepare("INSERT INTO event_feedback(event_participant_id, rate, useful, interesting, purpose, feed_back, sugest, product, app_url, web_id, created_by, created_on, deleted)
										VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");            
			$stmt->bind_param("issssssisssi", $ev_particpant_id, $rate, $useful, $interesting, $purpose, $feedback, $suggest, $product, $appurl, $webid, $create_by, $stat_deleted);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
	}
	
	//--------------------------------------------------------GAHARU---------------------------------------------------//
	
    //Method to get faculy details by username
    public function getFaculty($username){
        $stmt = $this->con->prepare("SELECT * FROM faculties WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        $faculty = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $faculty;
    }

    //Method to get faculty name by id
    public function getFacultyName($id){
        $stmt = $this->con->prepare("SELECT name FROM faculties WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $faculty = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $faculty['name'];
    }

    //Method to check the student username already exist or not
    private function isStudentExists($username) {
        $stmt = $this->con->prepare("SELECT id from students WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Method to check the faculty username already exist or not
    private function isFacultyExists($username) {
        $stmt = $this->con->prepare("SELECT id from faculties WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Checking the student is valid or not by api key
    public function isValidStudent($api_key) {
        $stmt = $this->con->prepare("SELECT id from students WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Checking the faculty is valid or not by api key
    public function isValidFaculty($api_key){
        $stmt = $this->con->prepare("SELECT id from faculties WHERE api_key=?");
        $stmt->bind_param("s",$api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }

    //Method to generate a unique api key every time
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }
}