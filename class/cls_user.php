<?php

class wh_user
{
var $id=0;
var $username='';
var $fullname='';

function wh_user()
{
	$this->id       = ''.@$_SESSION['id'];
	$this->username = ''.@$_SESSION['username'];
	$this->fullname = ''.@$_SESSION['fullname'];

	if($this->id=='') $this->id=0;
}

function present()
{
	return true;
	//return $this->id=='0'?false:true;
}

function login($user,$pass)
{
	$con=mysqli_connect("localhost","root","","denmark1");

	global $db;

	$sql="
		SELECT
			*
		FROM
			wh_user
		WHERE
			username='$user'
			AND
			password='$pass';
		";
	//$db->open($sql);
	//if(!(mysqli_query($con,$sql)) return false;
	//if(!$db->move_next()) return false;
	$row=mysqli_fetch_assoc(mysqli_query($con,$sql));
	
	$this->id=$row['id'];
	$this->username=$row['username'];
	
	$this->fullname=$row['fullname'];
	mysqli_close($con);

	return true;
}

}

?>
