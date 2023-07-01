<?php
class Doctor {	
   
	private $doctorTable = 'hms_doctor';
	private $conn;
	
	public function __construct($db){
        $this->conn = $db;
    }	    
	
	public function listDoctors(){
		
		$sqlWhere = '';
		if($_SESSION["role"] == 'doctor') { 
			$sqlWhere = " WHERE id = '".$_SESSION["userid"]."'";
		}	
		
		$sqlQuery = "SELECT * FROM ".$this->doctorTable." $sqlWhere ";
		
		if(!empty($_POST["search"]["value"])){
			$sqlQuery .= ' AND (id LIKE "%'.$_POST["search"]["value"].'%" ';
			$sqlQuery .= ' OR name LIKE "%'.$_POST["search"]["value"].'%" ';			
			$sqlQuery .= ' OR mobile LIKE "%'.$_POST["search"]["value"].'%" ';
			$sqlQuery .= ' OR address LIKE "%'.$_POST["search"]["value"].'%" ';
			$sqlQuery .= ' OR fee LIKE "%'.$_POST["search"]["value"].'%" ';
			$sqlQuery .= ' OR specialization LIKE "%'.$_POST["search"]["value"].'%") ';								
		}
		
		if(!empty($_POST["order"])){
			$sqlQuery .= 'ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$sqlQuery .= 'ORDER BY id DESC ';
		}
		
		if($_POST["length"] != -1){
			$sqlQuery .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}
		
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$result = $stmt->get_result();	
		
		$stmtTotal = $this->conn->prepare("SELECT * FROM ".$this->doctorTable." $sqlWhere " );
		$stmtTotal->execute();
		$allResult = $stmtTotal->get_result();
		$allRecords = $allResult->num_rows;
		
		$displayRecords = $result->num_rows;
		$records = array();		
		while ($doctor = $result->fetch_assoc()) { 				
			$rows = array();			
			$rows[] = $doctor['id'];
			$rows[] = ucfirst($doctor['name']);
			$rows[] = $doctor['address'];		
			$rows[] = $doctor['mobile'];	
			$rows[] = $doctor['fee'];	
			$rows[] = $doctor['specialization'];						
			$rows[] = '<button type="button" name="view" id="'.$doctor["id"].'" class="btn btn-info btn-xs view"><span class="glyphicon glyphicon-file" title="View"></span></button>';			
			$rows[] = '<button type="button" name="update" id="'.$doctor["id"].'" class="btn btn-warning btn-xs update"><span class="glyphicon glyphicon-edit" title="Edit"></span></button>';
			$rows[] = '<button type="button" name="delete" id="'.$doctor["id"].'" class="btn btn-danger btn-xs delete" ><span class="glyphicon glyphicon-remove" title="Delete"></span></button>';
			$records[] = $rows;
		}
		
		$output = array(
			"draw"	=>	intval($_POST["draw"]),			
			"iTotalRecords"	=> 	$displayRecords,
			"iTotalDisplayRecords"	=>  $allRecords,
			"data"	=> 	$records
		);
		
		echo json_encode($output);
	}
	
	public function getDoctor(){
		if($this->id) {
			$sqlQuery = "
				SELECT * FROM ".$this->doctorTable." 
				WHERE id = ?";			
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->bind_param("i", $this->id);	
			$stmt->execute();
			$result = $stmt->get_result();
			$record = $result->fetch_assoc();
			echo json_encode($record);
		}
	}
	
	public function insert(){
		
		if($this->name) {

			$stmt = $this->conn->prepare("
			INSERT INTO ".$this->doctorTable."(`name`, `email`, `mobile`, `address`, `fee`,`specialization`,`password`)
			VALUES(?,?,?,?,?,?,?)");
		
			$this->name = htmlspecialchars(strip_tags($this->name));
			$this->email = htmlspecialchars(strip_tags($this->email));
			$this->mobile = htmlspecialchars(strip_tags($this->mobile));
			$this->address = htmlspecialchars(strip_tags($this->address));	
			$this->fee = htmlspecialchars(strip_tags($this->fee));	
			$this->specialization = htmlspecialchars(strip_tags($this->specialization));
			$this->password = md5($this->password);				
			
			$stmt->bind_param("ssssiss", $this->name, $this->email, $this->mobile, $this->address, $this->fee, $this->specialization, $this->password);
			
			if($stmt->execute()){
				return true;
			}		
		}
	}
	
	public function update(){
		
		if($this->id) {		
			$passwordField = '';
			if($this->password){
				$passwordField = ", password = '".md5($this->password)."'";
			}
			
			$stmt = $this->conn->prepare("
				UPDATE ".$this->doctorTable." 
				SET name= ?, email = ?, mobile = ?, address = ?, fee = ?, specialization = ? $passwordField
				WHERE id = ?");
	 
			$this->id = htmlspecialchars(strip_tags($this->id));
			$this->name = htmlspecialchars(strip_tags($this->name));
			$this->email = htmlspecialchars(strip_tags($this->email));
			$this->mobile = htmlspecialchars(strip_tags($this->mobile));
			$this->address = htmlspecialchars(strip_tags($this->address));	
			$this->fee = htmlspecialchars(strip_tags($this->fee));	
			$this->specialization = htmlspecialchars(strip_tags($this->specialization));			
			
			$stmt->bind_param("ssssisi", $this->name, $this->email, $this->mobile, $this->address, $this->fee, $this->specialization, $this->id);
			
			if($stmt->execute()){
				return true;
			}
			
		}	
	}	
	
	public function delete(){
		if($this->id) {			

			$stmt = $this->conn->prepare("
				DELETE FROM ".$this->doctorTable." 
				WHERE id = ?");

			$this->id = htmlspecialchars(strip_tags($this->id));

			$stmt->bind_param("i", $this->id);

			if($stmt->execute()){
				return true;
			}
		}
	}
}
?>