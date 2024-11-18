<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include('../include/connect.php');
include("../include/functions.php");

$user_id = filter_var($_GET['usr_id'], FILTER_SANITIZE_NUMBER_INT);
include('check_api_token_get.php');
if($_GET['page'] >0)
{
    $page = filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT);
}else{
    $page =1;
}
$limit = 10;
$offset = ($page-1)*$limit;


if(isset($_GET['usr_id']) != "" && $_GET['type']=='transaction') {
 
            $query = "SELECT COUNT(id) as rowNum FROM user_transaction WHERE user_id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $res1 = mysqli_fetch_assoc($result);
            $allRecrods = $res1['rowNum'];
            $totoalPages = ceil($allRecrods / $limit);
            
            $data = array();
            $data['totoalPages'] = $totoalPages;
            $data['currentPage'] = $page;
            $data['perPage'] = $limit;
            $data['totalRecords'] = $allRecrods;
            
            mysqli_stmt_close($stmt);

             
             
             $qry = "SELECT * FROM user_transaction WHERE user_id = ? ORDER BY id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
             
             $data["records"]=array();
             if(mysqli_num_rows($result)>0){
                 
                 while ($row = mysqli_fetch_array($result)){
                     
		            if($row['type'] =='bid'){
		                if($row['starline']==1){
		                    $narration = 'STARLINE ('.get_Starlinetime($row['game_id']).')';
		                }elseif($row['starline']==2){
		                $narration =  "MJ ".get_Jackpottime($row['game_id']);
		                }else{
		                $narration = 'Bid - '.get_gameNameById($row['game_id']).' - '.$row['game_type'];
		                }
		            }
		            
		            if($row['type'] =='deposit' && $row['title'] =='Debited By Admin'){
                    $narration = 'Debited By Admin';
                    }
                    		            
                    if($row['type'] =='deposit' && $row['title'] =='upi'){
                    $narration = 'Credited By UPI';
                    }
                    		            
                    if($row['type'] =='deposit' && $row['title'] ==''){
                    $narration = 'Credited By Admin';
                    }
		            
		            if($row['type'] =='withdraw'){
		            $narration = 'Withdraw';
		            }
		            
		            if($row['type'] =='withdraw_rejected'){
		            $narration = 'Withdraw Rejected By Admin';
		            }
					
					if($row['game_type'] =='withdraw_cancelled'){ 
		            $narration = 'Withdraw Cancelled By User';
		            }
					
					
		            
		            if($row['type'] =='win'){
		                if($row['starline']==1){
		                    $narration = 'STARLINE ('.get_Starlinetime($row['game_id']).')';
		                }else{
		                $narration = 'Win - '.get_gameNameById($row['game_id']).' - '.$row['game_type'];
		                }
		            }
		            
		            if($row['type'] =='revert'){
		            $narration = 'Revert Back ';
		            }
		            
		            
             
                $data_item=array(
                    "id" => $row['id'],
                    "narration" => $narration,
                    "digit" => $row['digit'],
					"game_type" => $row['game_type'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "balance" => $row['balance'],
                    "date" => date('d M, Y', strtotime($row['date'])),
                    "time" => $row['time']
                );
                array_push($data["records"], $data_item);
             }
             
             
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             mysqli_stmt_close($stmt);
             
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='bidding') {
             
             
            // Count of all records
            $query = "SELECT COUNT(id) as rowNum FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '0'";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $res1 = mysqli_fetch_assoc($result);
            $allRecrods = $res1['rowNum'];
            mysqli_stmt_close($stmt);
            
            $totoalPages = ceil($allRecrods / $limit);
            
            $data = array();
            $data['totoalPages'] = $totoalPages;
            $data['currentPage'] = $page;
            $data['perPage'] = $limit;
            $data['totalRecords'] = $allRecrods;
            
            // Fetch records with pagination
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '0' ORDER BY id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data["records"] = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                     
		                $narration = get_gameNameById($row['game_id']).' - '.$row['game_type'];;
		            
		         
		         if($row['win']=='' || $row['win']=='NULL'){
		              $game_result = 'Pending';
		          }elseif($row['win']=='0')
		          {
		              $game_result = 'LOSE';
		          }else{
		              $game_result = $row['win'];
		          }
             
                $data_item=array(
                    "id" => $row['id'],
                    "narration" => $narration,
                    "digit" => $row['digit'],
                    "game_type" => $row['game_type'],
					"digit_type" => $row['game_type'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "result" => $game_result,
                    "bid_time" => date('d/m/Y h:i A',strtotime($row['timestamp'])),
                    "bid_on" => date('d/m/Y',strtotime($row['timestamp'])).'('.date('l', strtotime($row['timestamp'])).')',
                    "play_for" => date('d/m/Y',strtotime($row['date'])).'('.date('l', strtotime($row['date'])).')'
                );
                array_push($data["records"], $data_item);
             }
             
          
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             mysqli_stmt_close($stmt);
             
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='starline_bidding') {
             
             
            // Count of all records
            $query = "SELECT COUNT(id) as rowNum FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '1'";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $res1 = mysqli_fetch_assoc($result);
            $allRecrods = $res1['rowNum'];
            mysqli_stmt_close($stmt);
            
            $totoalPages = ceil($allRecrods / $limit);
            
            $data = array();
            $data['totoalPages'] = $totoalPages;
            $data['currentPage'] = $page;
            $data['perPage'] = $limit;
            $data['totalRecords'] = $allRecrods;
            
            // Fetch records with pagination
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '1' ORDER BY id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data["records"] = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                     
		                    $narration = 'STARLINE ('.get_Starlinetime($row['game_id']).')';
		            
		         
		         if($row['win']==''){
		              $game_result = 'Pending';
		          }elseif($row['win']=='0')
		          {
		              $game_result = 'LOSE';
		          }else{
		              $game_result = $row['win'];
		          }
             
                $data_item=array(
                    "id" => $row['id'],
                    "narration" => $narration,
                    "digit" => $row['digit'],
                    "game_type" => $row['game_type'],
					"digit_type" => $row['game_type'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "result" => $game_result,
                    "bid_time" => date('d/m/Y h:i A',strtotime($row['timestamp'])),
                    "bid_on" => date('d/m/Y',strtotime($row['timestamp'])).'('.date('l', strtotime($row['timestamp'])).')',
                    "play_for" => date('d/m/Y',strtotime($row['date'])).'('.date('l', strtotime($row['date'])).')'
                );
                array_push($data["records"], $data_item);
             }
             
          
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             
             mysqli_stmt_close($stmt);
             
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='jackpot_bidding') {
             
             
            // Count of all records
            $query = "SELECT COUNT(id) as rowNum FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '2'";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $res1 = mysqli_fetch_assoc($result);
            $allRecrods = $res1['rowNum'];
            mysqli_stmt_close($stmt);
            
            $totoalPages = ceil($allRecrods / $limit);
            
            $data = array();
            $data['totoalPages'] = $totoalPages;
            $data['currentPage'] = $page;
            $data['perPage'] = $limit;
            $data['totalRecords'] = $allRecrods;
            
            // Fetch records with pagination
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND type = 'bid' AND starline = '2' ORDER BY id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data["records"] = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                     
		                $narration =  "MJ ".get_Jackpottime($row['game_id']);
		            
		         
		         if($row['win']==''){
		              $game_result = 'Pending';
		          }elseif($row['win']=='0')
		          {
		              $game_result = 'LOSE';
		          }else{
		              $game_result = $row['win'];
		          }
             
                $data_item=array(
                    "id" => $row['id'],
                    "narration" => $narration,
                    "digit" => $row['digit'],
                    "game_type" => $row['game_type'],
					"digit_type" => $row['game_type'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "result" => $game_result,
                    "bid_time" => date('d/m/Y h:i A',strtotime($row['timestamp'])),
                    "bid_on" => date('d/m/Y',strtotime($row['timestamp'])).'('.date('l', strtotime($row['timestamp'])).')',
                    "play_for" => date('d/m/Y',strtotime($row['date'])).'('.date('l', strtotime($row['date'])).')'
                );
                array_push($data["records"], $data_item);
             }
             
          
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             mysqli_stmt_close($stmt);
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='fund'){
    
            // Count of all records
            $query = "SELECT COUNT(id) as rowNum FROM user_transaction WHERE user_id = ? AND (type = 'deposit' OR type = 'withdraw')";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $res1 = mysqli_fetch_assoc($result);
            $allRecrods = $res1['rowNum'];
            mysqli_stmt_close($stmt);
            
            $totoalPages = ceil($allRecrods / $limit);
            
            $data = array();
            $data['totoalPages'] = $totoalPages;
            $data['currentPage'] = $page;
            $data['perPage'] = $limit;
            $data['totalRecords'] = $allRecrods;
            
            // Fetch records with pagination
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND (type = 'deposit' OR type = 'withdraw') ORDER BY id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data["records"] = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                     if($row['status'] ==1)
                     {
                        $status = 'Pending';
                     }elseif($row['status'] ==2)
                     {
                         $status = 'Approved';
                     }else{$status = 'Canceled';}
                     
                     if($row['type'] == 'deposit')
                     {
                         $status = 'Success';
                     }
                     

                $data_item=array(
                    "id" => $row['id'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "type" => $row['type'],
                    "status" => $status,
                    "remark" => $row['remark'],
                    "date" => date('d M, Y', strtotime($row['date'])),
                    "time" => $row['time']
                );
                array_push($data["records"], $data_item);
             }
             
             $data["pagination"]=array();
             $data_pagination=array(
                    "totoalPages" =>$totoalPages,
                    "currentPage" => $page,
                    "totalRecords"=> $allRecrods
                );
                array_push($data["pagination"], $data_pagination);
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             
             mysqli_stmt_close($stmt);
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='deposit'){
    
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND type = 'deposit' ORDER BY id DESC";
            $stmt = mysqli_prepare($con, $qry);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = array();
            $data["records"] = array();
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                     

                $data_item=array(
                    "id" => $row['id'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "remark" => $row['remark'],
                    "date" => date('d M, Y', strtotime($row['date'])),
                    "time" => $row['time']
                );
                array_push($data["records"], $data_item);
             }
             
             
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             }
             mysqli_stmt_close($stmt);
}elseif(isset($_GET['usr_id']) != "" && $_GET['type']=='withdraw')
{
            $qry = "SELECT * FROM user_transaction WHERE user_id = ? AND type = 'withdraw' ORDER BY id DESC";
                $stmt = mysqli_prepare($con, $qry);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                $data = array();
                $data["records"] = array();
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                     

                $data_item=array(
                    "id" => $row['id'],
                    "trans_type" => ucfirst($row['debit_credit']),
                    "amount" => number_format($row['amount'],2),
                    "date" => date('d M, Y', strtotime($row['date'])),
                    "time" => $row['time']
                );
                array_push($data["records"], $data_item);
             }
             
            
                http_response_code(200);
                echo json_encode($data);
                mysqli_close($con);
                
             }else{
                 $data_item=array(
                    "error" => "No record found"
                );
                //array_push($data["records"], $data_item);
                echo json_encode($data);
             } 
             mysqli_stmt_close($stmt);
}else{
            $data=array();
             $data["records"]=array();
             $data_item=array(
                    "error" => "Wrong Request"
                );
                array_push($data["records"], $data_item);
                echo json_encode($data);
}
mysqli_close($con);
 
?>
