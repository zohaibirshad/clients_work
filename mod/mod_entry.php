<?php

class mod_entry extends module {

function from_db($text)
{
	$text=str_replace('[TREF]','[TXT]',$text);
	$text=str_replace('[KEY[','[K[',$text);
	$text=str_replace('[OS[','[IMG[',$text);
	return $text;
}

function to_db($text)
{
	$text=str_replace('[TXT]','[TREF]',$text);
	$text=str_replace('[K[','[KEY[',$text);
	$text=str_replace('[IMG[','[OS[',$text);
	return $text;
}

function display_email($email)
{
	$email=str_replace('@','&nbsp;AT&nbsp;',$email);
	$email=str_replace('.','&nbsp;DOT&nbsp;',$email);

	return $email;
}




function delete($id)
{
	$this->db->execute("DELETE FROM wh_textdata WHERE id=$id");
	$this->html_redirect('/?mod=list');
}




function insert()
{
	$con=mysqli_connect("localhost","root","","denmark1");

	$title=request('title');
	$text=request('text');
	$text=$this->to_db($text);

	if($title=='') $title='TITEL';
	if($text=='') $text='<p>TEKST</p>';

	$title=mysqli_real_escape_string($title);
	$text=mysqli_real_escape_string($text);

	$sql="INSERT INTO wh_textdata (title,content) VALUES('$title','$text')";
mysqli_query($con,$sql);
	$rs=mysqli_query($con,"SELECT MAX(id) AS mid FROM wh_textdata");
	$row=mysqli_fetch_Assoc($rs);
	$id=$row['mid'];
	mysqli_close($con);

	$this->html_redirect('/?mod=entry&id='.$id.'&c=edit&x='.rand(1000,9999));
}


function newtext()
{
	$tpl=$this->page->read_template('entry_new');




	$this->page->title='Opret';
	$this->page->content=utf8_encode($tpl);
}




function rem_os($id)
{
	$con=mysqli_connect("localhost","root","","denmark1");
	$os=request('os');
	if(!is_numeric($os)) return;
	mysqli_query($con,"DELETE FROM wh_text_os WHERE text_id=$id AND os_id=$os");
	$this->html_redirect('/?mod=entry&id='.$id.'&c=edit&x='.rand(1000,9999));
}

function add_os($id)
{
	$con=mysqli_connect("localhost","root","","denmark1");
	$os=request('os');
	if(!is_numeric($os)) return;
	mysqli_query("INSERT INTO wh_text_os VALUES($id,$os)");
	$this->html_redirect('/?mod=entry&id='.$id.'&c=edit&x='.rand(1000,9999));
}

function edit($id)
{
	$con=mysqli_connect("localhost","root","","denmark1");
	$tpl=$this->page->read_template('entry_edit');

	$rs=mysqli_query($con,"SELECT * FROM wh_textdata WHERE id=$id");
	if(!$rs) return;
	$row=mysqli_fetch_assoc($rs);
	$title=$row['title'];
	$text=$row['content'];
	$text=$this->from_db($text);
	$tpl=str_replace('[ENTRY[ID]]',$row['id'],$tpl);
	$tpl=str_replace('[ENTRY[TITLE]]',$title,$tpl);
	$tpl=str_replace('[ENTRY[TEXT]]',$text,$tpl);
	mysqli_close($con);

	$os='';
	$oslist=array();
	$rs=mysqli_query($con,"SELECT o.* FROM wh_text_os t,wh_os o WHERE t.text_id=$id AND t.os_id=o.id ORDER BY o.seq");
	while($row=mysqli_fetch_assoc($rs)){
		$oslist[]=$row['id'];
		$os.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
	mysqli_close($con);
	$tpl=str_replace('[ENTRY[C-OS]]',$os,$tpl);

	$os='';
	$rs=mysqli_query($con,"SELECT * FROM wh_os ORDER BY seq");
	while($row=mysqli_fetch_assoc($rs)){
		if(!in_array($row['id'],$oslist))
			$os.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
	mysqli_close($con);
	$tpl=str_replace('[ENTRY[P-OS]]',$os,$tpl);

	$this->page->title='Rediger "'.$title.'"';
	$this->page->content=utf8_encode($tpl);
}

function update($id)
{
	$con=mysqli_connect("localhost","root","","denmark1");
	$title=request('title');
	$text=request('text');

	$text=$this->to_db($text);

	$title=mysqli_real_escape_string($title);
	$text=mysqli_real_escape_string($text);

	mysqli_query($con,"UPDATE wh_textdata SET title='$title',content='$text' WHERE id=$id");

	$this->html_redirect('/?mod=entry&id='.$id.'&c=edit&x='.rand(1000,9999));
}

function display($id)
{
	$con=mysqli_connect("localhost","root","","denmark1");

	// Read entry template
	$tpl=$this->page->read_template('entry');

	// Lookup entry
	$sql="
		SELECT
			*
		FROM
			wh_textdata
		WHERE
			id=$id
		";
	$rs=mysqli_query($con,$sql);
	if(!$rs) return;
$row=mysqli_fetch_assoc($rs);
	// Get and normalize date/time
	$d=$row['date_created'];
	if($d=='0000-00-00 00:00:00') $d='<font color=white>For l�nge siden</font>';

	// Get and normalize text content
	$content=$rs->field('content');
	if($content{0}!='<') $content="<p>$content</p>";
	$content=str_ireplace('<br>','<br />',$content);

	// Insert entry data
	$tpl=str_replace('[ENTRY[ID]]',   $rs->field('id'),$tpl);
	$tpl=str_replace('[ENTRY[DATE]]', $d,$tpl);
	$tpl=str_replace('[ENTRY[TITLE]]',$rs->field('title'),$tpl);
	$tpl=str_replace('[ENTRY[TEXT]]', $content,$tpl);
	$this->page->title=utf8_encode($rs->field('title'));
	$rs->close();

	// Get OS list for entry
	$os='';
	$sql="
		SELECT
			o.*
		FROM
			wh_os o,
			wh_text_os t
		WHERE
			t.text_id=$id
			AND
			t.os_id=o.id
		ORDER BY
			o.seq,
			o.name
		";
	$rs=mysqli_query($con,$sql);
	while ($row=mysqli_fetch_assoc($rs)) {
		# code...
	if($os!='') $os.=', ';
			$os.=$row['shortname'];
		}
	
	mysqli_close($con);
	if($os=='') $os='Generelt';
	$tpl=str_replace('[ENTRY[OSLIST]]', $os,$tpl);

	// Get Comments for entry
	$clist='';
	$sql="
		SELECT
			*
		FROM
			wh_comment
		WHERE
			textid=$id
			AND
			active=1
		ORDER BY
			date_created DESC
		";
	$rs=mysqli_query($con,$sql);
//die($sql);
	while($row=mysqli_fetch_assoc($rs)){

		//$t='<tr><td style="border-top:1px solid #999999">[COMMENT[NAME]]</td></tr><tr><td>[COMMENT[EMAIL]]</td></tr><tr><td style="background-color:#ffffff">[COMMENT[TEXT]]</td></tr>';
		$t=$this->page->read_template('entry_comment_item');
		$t=str_replace('[COMMENT[ID]]',   $row['id'],$t);
		$t=str_replace('[COMMENT[NAME]]', $row['username'],$t);
		$t=str_replace('[COMMENT[EMAIL]]',$this->display_email($row['useremail']),$t);
		$t=str_replace('[COMMENT[TEXT]]', $row['comment'],$t);
		$clist.=$t;

	}
	mysqli_close($con);
	$tpl=str_replace('[ENTRY[COMMENTS]]',$clist,$tpl);
	apply_section($tpl,'HASCOMMENT',$clist==''?1:0);

	// Apply page content
	$this->page->content=utf8_encode($tpl);
}



function run($cmd)
{
	$id=request('id');
	switch($cmd){
	case 'delete':
		$this->delete($id);
		break;

	case 'new':
		$this->newtext();
		break;

	case 'insert':
		$this->insert();
		break;

	case 'edit':
		$this->edit($id);
		break;

	case 'update':
		$this->update($id);
		break;

	case 'addos':
		$this->add_os($id);
		break;

	case 'remos':
		$this->rem_os($id);
		break;

	default:
		$this->display($id);
		break;
	}

}

}

?>