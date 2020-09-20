<?php 
//echo "test";exit;
// require('includes/application_top.php');

$username = "root";
$password = "";
$hostname = "localhost";
$dbname = "db name here";
  
  
//    //connection to the database
$conn = mysqli_connect($hostname, $username, $password, $dbname)or die(mysqli_error());
//  $link = @mysql_connect($hostname,$username, $password);
//  $conn = mysql_select_db($dbname, $link);  

?>

<table border="1" class="table table-striped table-hover" width="400" >
    <thead>
        <tr class="warning">
            <th class="inbox-small-cells">
                <input type="checkbox" class="mail-checkbox">
            </th>
            <th ><i class="fa fa-star"></i></th>
            <th >#</th>
            <th >Sender</th>
            <th >Subject</th>
            <th ><i class="fa fa-paperclip"></i></th>
            <th >Date</th>  
            <th >Attachment</th>  
            <th >Message</th>     
        </tr>
     </thead>
<tbody>
 <?php

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
//$hostname = '{imap.gmail.com:995/pop3/ssl}INBOX';
$username = 'your email here';
$password = 'your password here';

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
$emails = imap_search($inbox,'ALL');
//$emails = imap_search($inbox,'UNSEEN');
//$x=count($MB);
if($emails) {
rsort($emails);
/* for every email... */
$email_string = "";
foreach($emails as $email_number) {
//$email_number=$emails[0];
//print_r($emails);
/* get information specific to this email */
$overview = imap_fetch_overview($inbox,$email_number,0);
$message = imap_fetchbody($inbox,$email_number, 1, FT_PEEK);
$str_msg = nl2br(stripcslashes(htmlspecialchars($message)));    

$email_number;
$overview[0]->subject;
$overview[0]->from;
$overview[0]->date;
$overview[0]->size ;


/* get mail structure */
        $structure = imap_fetchstructure($inbox, $email_number);

        $attachments = array();

        /* if any attachments found... */
        if(isset($structure->parts) && count($structure->parts)) 
        {
            for($i = 0; $i < count($structure->parts); $i++) 
            {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );

                if($structure->parts[$i]->ifdparameters) 
                {
                    foreach($structure->parts[$i]->dparameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'filename') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if($structure->parts[$i]->ifparameters) 
                {
                    foreach($structure->parts[$i]->parameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'name') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if($attachments[$i]['is_attachment']) 
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

                    /* 3 = BASE64 encoding */
                    if($structure->parts[$i]->encoding == 3) 
                    { 
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    /* 4 = QUOTED-PRINTABLE encoding */
                    elseif($structure->parts[$i]->encoding == 4) 
                    { 
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }

?>

        <tr class="text-info">
            <td class="inbox-small-cells">
                <input type="checkbox" class="mail-checkbox">
            </td>
            <td>
              <i class="fa fa-star"></i>
            </td>
            <td> <?php echo  $email_number;?></td>
            <td><?php echo  $overview[0]->from;?></a></td>
            <td><?php echo  $overview[0]->subject; ?></td>
            <td class="view-message  inbox-small-cells">
              <i class="fa fa-paperclip"></i>
            </td>
            <td><?php echo  $overview[0]->date;?> </td>
            <td>
            <?php
            /* iterate through each attachment and save it */
        foreach($attachments as $attachment)
        {
            if($attachment['is_attachment'] == 1)
            {
                $filename = str_replace(" ", "-",$attachment['name']);

                if(empty($filename)) $filename = str_replace(" ", "-",$attachment['filename']);

                if(empty($filename)) $filename = time() . ".dat";
                $folder = "attachment";
                if(!is_dir($folder))
                {
                     mkdir($folder);
                }
                $fp = fopen("./". $folder ."/". $email_number . "-" . $filename, "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
                $src_path = "./". $folder ."/". $email_number . "-" . $filename;
                $f_t = explode('.', $filename);
                if ($f_t[1]=='gpeg' || $f_t[1]=='png' || $f_t[1]=='gif' || $f_t[1]=='jpg') {
                echo '<a href="'.$src_path.'" target="blank"><img src="'.$src_path.'" width="100" height="100"></a>';
                } else {
                echo "<a href=".$src_path."> Save (".$filename.")</a>";
                }
            }
        }
            ?>
          </td>
            <td><?php echo  $str_msg;?> </td>
        </tr>
<?php
    
 $old_date_timestamp = strtotime($overview[0]->date);
 $new_date = date('Y-m-d H:i:s', $old_date_timestamp);  

  $imap_sql_qry = "select * from imap_emails where id = '".$email_number."'";
    $imap_email_qry = mysqli_query($conn,$imap_sql_qry);
    $imap_email = mysqli_fetch_assoc($imap_email_qry);
    if(!mysqli_num_rows($imap_email_qry)){
    
echo "INSERT INTO `imap_emails`(`id`, `sender_name`,  `send_from`, `subject`,`body`, `date`) VALUES ('".$email_number."','".$overview[0]->from."','".$overview[0]->from."','". $overview[0]->subject."','".$str_msg."','".$new_date."')";

        $sql_email_insert = "INSERT INTO `imap_emails`(`id`, `sender_name`,  `send_from`, `subject`,`body`, `date`) VALUES ('".$email_number."','".$overview[0]->from."','".$overview[0]->from."','". $overview[0]->subject."','".$str_msg."','".$new_date."')";
        mysqli_query($conn,$sql_email_insert);

      }
  
  $email_string .= 'Subject: '.$overview[0]->subject;
  $email_string .= 'From: '.$overview[0]->from;
  $email_string .= 'Message: '.$message;
  $email_string .= 'Date: '.$overview[0]->date;

  }
?>
</tbody></table>
<?php
 }
if($email_string == ''){
      $mail_str ="No Communication Found";
      $to = "";
      $subject = "Imap Emails( No Updated Emails found ) ";

      $headers .= "From Gemwares Imap Emails \r\n";
      $headers .= "X-Priority: 3\r\n";
      $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
      $headers .= 'Reply-To: ' . $to . "\r\n";


    //  mail($to, $subject, $mail_str, $headers);

 }else{
      $mail_str = $email_string;
      $to = "";
      $subject = "Imap Emails";

      // $headers .= "From Gemwares Imap Emails \r\n";
      // $headers .= "X-Priority: 3\r\n";
      // $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
      // $headers .= "MIME-Version: 1.0\r\n";
      // $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
      // $headers .= 'Reply-To: ' . $to . "\r\n";


    //  mail($to, $subject, $mail_str, $headers);
  }
?>