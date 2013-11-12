<? 

error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
   handlePost(); 
} else if ($_SERVER["REQUEST_METHOD"] = 'GET') {
   handleGet();
} else {
   echo "I don't know what you got";
}

function handleGet() {
	if (!isset($_GET["syskey"])) {
		handleNothing();
	} else {
		handleQuery();
	}
}

function handleNothing() {
	include 'frontpage.html';
}

function handleQuery() {

	$syskey = $_REQUEST['syskey'];

	preg_match("/^[a-zA-Z\-_0-9]{1,30}(%?)$/", $syskey) || die("Illegal syskey");

	include 'connect.php';


	//$qid = mysql_query("select * from iplog where syskey like '$syskey' order by updated desc limit 0, 10");
	$qid = mysql_query("select ip.* from iplog ip left join iplog ip2 on (ip.syskey = ip2.syskey and ip.updated < ip2.updated) where ip2.id is null and ip.syskey like '$syskey' limit 0, 10");
	$nums = mysql_num_rows($qid);
	if ($nums == 0) {
	   echo "Nothing known about syskey " . $syskey . "<br/>";
	   ?><a href="./">Back</a><?
	} else {
           while($row = mysql_fetch_object($qid)) {
	        $format = isset($_GET['format']) && $_GET['format'] == "json" ? 'json' : 'html';

	        if ($format == 'html') {
       		     echo "Last known ip for syskey $row->syskey: $row->ip Updated: $row->updated, created: $row->created<br/>";
                } else {
                     echo "{\"syskey\":\"$row->syskey\",\"ip\":\"$row->ip\",\"updated\":\"$row->updated\",\"created\":\"$row->created\"}";
                }
           } while ($row != null);
           if ($format == "html") {
       	        ?><a href="./">Back</a><?
           }
    } 
}

function handlePost() {
	isset($_POST["syskey"]) || die("syskey required");
	isset($_POST["ip"]) || die("ip required");

	$ip = $_POST['ip'];
	$syskey = $_POST['syskey'];

	preg_match("/^[a-zA-Z\-_0-9]{1,30}$/", $syskey) || die("Illegal syskey");
	preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip) || die("Illegal ip");

	include 'connect.php';

	$qid = mysql_query("select * from iplog where syskey = '$syskey' order by updated desc limit 0, 1");

	$nums = mysql_num_rows($qid);
	if ($nums == 0) {
	   echo "First time for syskey " . $syskey . "<br/>";
	   $sql = "insert into iplog (syskey, ip, updated) values ('$syskey', '$ip', now())";
	   mysql_query($sql);
	} else {
	   echo "Have seen this syskey $syskey before<br/>";
	   $row = mysql_fetch_object($qid);
	   if ($row->ip == $ip) {
	      echo "Updating last updated for syskey $syskey<br/>";
	      $sql = "update iplog set updated = now() where id = '$row->id'";
	      mysql_query($sql);
	   } else {
	      echo "Inserting new ip for syskey $syskey<br/>";
	      $sql = "insert into iplog (syskey, ip, updated) values ('$syskey', '$ip', now())";
	      mysql_query($sql);
	   }
	}
}
?>
