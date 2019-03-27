<?php
include 'connections.php';
header('Access-Control-Allow-Origin: *');  
//include '../../../db_connection.php';

session_start();
$user_id=$_SESSION['user_id'];
  //  $user_id=1;
    $all_affix_glossary=array();
    $meanings=$no_meanings=array();	
    $matching_meanings= array();
    $total=array();
    $latters=array();
    $no_sidechars=array();
    $basic_lang=array();
    $llb_glosaary=array();
    $indv_glossary=array();
    $comb_glossary=array();
    $all_comb_glossary=array();
    $prefix_glossary=array();
    $suffix_glossary=array();
    $words_glossary=array();
    $admin_book_content=array(); // Before Search
    $user_book_content=array(); // Before Search
    $baseWord = array();
    $next = array();
    $prev = array();
    $leftbasewords = array();
    $rightbasewords = array();
    $match_indv_fire=array();
    $match_indv_fire['article_determiner']=0;
    $match_indv_fire['preposition']=0;
    $match_indv_fire['interjection']=0;
    $match_indv_fire['pronoun']=0;
    $match_indv_fire['conjuction']=0;
    $match_indv_fire['contraction']=0;
    $match_indv_fire['foreign_word']=0;
    $match_indv_fire['proverb']=0;
    $match_indv_fire['abbreviation']=0;
    $match_indv_fire['acronym']=0;
    $match_indv_fire['symbol']=0;
    $match_indv_fire['combiningfire']=0;
    $match_indiv_fire['affixfire']=0;
    $match_indiv_fire['llbfire']=0;
    
    $words=array();
    $allwords=array();
    $adminBookcontent=array(); // After Search
    $userBookcontent=array(); // After Search
    $pre_suf_combine_form=array();
    $combining_form_word=array();
    $combining_form_list=array();
    $all_user_descp=array();
    $match_lang1=array();
    $match_lang2=array();
    $admin_book="";
    $user_book="";
if(@$_GET['fieldKey']=='get_basic_lang'){
    $sql="SELECT lang_basic_id,word_form,word_form_descp FROM `tbl_lang_basic`";
    $result1 = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result1)){
        $basic_lang[]=$row;
    }
    $array=array('basic_lang'=>$basic_lang);
	echo json_encode($array);
}
else if(@$_GET['fieldKey']=='get_llbglosaary'){
    $sql="SELECT list_of_base_word_name,list_of_base_word_id FROM tbl_list_of_base_word_new WHERE reasons !='' ";
    $result2 = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result2)){
        $row['active']=0;
        if($row['list_of_base_word_id']==$_GET['list_of_base_word_id']){
            $row['active']=1;
        }
        $llb_glosaary[]=$row;
    }
	echo json_encode($llb_glosaary);
}

else if(@$_GET['fieldKey']=='get_indiv_glossary'){
    $column=$_GET['column'];
    $select="";
    $sql="";
    $condition=$column."!='' ORDER BY ".$column." ASC ";
    if($column=='article_determiner'){
        $select='article_determiner , article_determiner_def as def';
    }else if($column=='interjection'){
        $select='interjection,interjection_def as def';
    }else if($column=='preposition'){
        $select='preposition,preposition_def as def';
    }else if($column=='pronoun'){
        $select='pronoun,pronoun_def as def';
    }else if($column=='conjuction'){
        $select='conjuction,conjuction_def as def';
    }else if($column=='contraction'){
        $select='contraction,contraction_def as def,contraction_status as fonts';
    }else if($column=='foreign_word'){
        $select='foreign_word,foreign_word_def as def,foreign_word_status as fonts';
    }else if($column=='proverb'){
        $select='proverb,proverb_statement,proverb_def as def,proverb_status as fonts';
    }else if($column=='abbreviation'){
         $select='ig.indi_w_glossary_id,ig.list_of_base_word_id,ig.abbreviation,ig.abbreviation_def as def,ig.abbreviation_status as fonts, ig.acronym,ig.acronym_def as def1, ig.acronym_status as fonts1,ig.symbol,ig.symbol_def as def2, ig.symbol_status as fonts2';
        // $condition="abbreviation!='' || acronym!='' || symbol!='' ORDER BY  indi_w_glossary_id ASC ";
        $sql="SELECT ".$select." FROM tbl_indi_w_glossary as ig INNER   JOIN tbl_list_of_base_word_new as bl ON bl.list_of_base_word_id=ig.list_of_base_word_id ";
        $sql.=" WHERE ( ig.abbreviation!='' || ig.acronym!='' || ig.symbol!='') ";
        $sql.=" ORDER BY ig.list_of_base_word_id ASC,ig.indi_w_glossary_id ASC";
        // SELECT ig.* FROM tbl_indi_w_glossary as ig INNER 
        // JOIN tbl_list_of_base_word_new as bl ON bl.list_of_base_word_id=ig.list_of_base_word_id 
        // ORDER BY ig.list_of_base_word_id ASC,ig.indi_w_glossary_id ASC
        
    }
    $list_of_base_word_id=$_GET['list_of_base_word_id'];
    $proverb_words="";
    if($column!='abbreviation'){
       $sql="SELECT indi_w_glossary_id,list_of_base_word_id, ".$select." FROM `tbl_indi_w_glossary` WHERE ".$condition;//.$column."!='' ORDER BY ".$column." ASC ";
    }
    //echo $sql;
    
    $result1 = mysqli_query($conn, $sql);
    $numb=1;
    $str="";
    while($row=mysqli_fetch_assoc($result1)){
        $strs=substr(strtolower($row[$column]),0,1);
        if($str!=$strs){
            $row1['list_of_base_word_id']=0;
            $row1[$column]='';
            array_push($indv_glossary,$row1);
        }
        $active=0;
        if($row['list_of_base_word_id']==$list_of_base_word_id){
            $active=1;
        }
        if($column=='proverb'){
            $val=$row['proverb'];
            if($proverb_words==$row['proverb']){
                $val="";
                $numb=$numb+1;
                $row['number']=$numb;
            }else{
                $row['number']=1;
            }
            $proverb_words=$row['proverb'];
            $row['proverb']=$val;
        }
        $numb=1;
        $row['active']=$active;
        $indv_glossary[]=$row;
        $str=substr(strtolower($row[$column]),0,1); 
    }
    echo json_encode($indv_glossary);
}else if(@$_GET['fieldKey']=='get_comb_w_glossary'){
    $column=$_GET['column'];
    $select="";
    $condition="";
    if($column=='person'){
        $select='person,person_status as font, place, place_status as font1';
        $condition="person!='' || place!=''  ORDER BY person ASC ,comb_w_glossary_id ASC";
    }
    else if($column=='giver'){
        $select='giver,giver_status as font, receiver, receiver_status as font1';
        $condition="giver!='' || receiver!=''  ORDER BY giver ASC ,comb_w_glossary_id ASC";
    }
    else if($column=='common'){
        $select='common,common_status as font, male, male_status as font1, female, female_status as font2, neutral, neutral_status as font3, wife, wife_status as font4, widow, widow_status as font5';
        $condition="common!='' || male!='' || female!='' || neutral!='' || wife!='' || widow!='' ORDER BY common ASC ,comb_w_glossary_id ASC";
    }
    else if($column=='singular'){
        $select='singular,singular_status as font, plural, plural_status as font1';
        $condition="singular!='' || plural!=''  ORDER BY singular ASC ,comb_w_glossary_id ASC";
    }
    else if($column=='diminutive_singular'){
        $select='diminutive_singular,diminutive_slr_status as font, diminutive_plural, diminutive_plr_status as font1';
        $condition="diminutive_singular!='' || diminutive_plural!=''  ORDER BY diminutive_singular ASC ,comb_w_glossary_id ASC";
    }
    $list_of_base_word_id=$_GET['list_of_base_word_id'];
    $proverb_words="";
    $sql="SELECT comb_w_glossary_id,list_of_base_word_id, ".$select." FROM `tbl_comb_w_glossary` WHERE ".$condition;
    $result1 = mysqli_query($conn, $sql);
    $numb=1;
    $str="";
    while($row=mysqli_fetch_assoc($result1)){
        $active=0;
        $padding_buttom=0;
        $strs=substr(strtolower($row[$column]),0,1);
        if($str!=$strs){
            $padding_buttom=1;
            // $row['list_of_base_word_id']=0;
            // $row[$column]='';
            // array_push($indv_glossary,$row1);
        }
        if($row['list_of_base_word_id']==$list_of_base_word_id){
            $active=1;
        }
        $row['active']=$active;
        $row['padding_buttom']=$padding_buttom;
        $comb_glossary[]=$row;
        $str=substr(strtolower($row[$column]),0,1); 
    }
    echo json_encode($comb_glossary);
}else if(@$_GET['fieldKey']=='get_all_comb_w_glossary'){
    $list_of_base_word_id=$_GET['list_of_base_word_id'];
    $proverb_words="";
    $sql="SELECT * FROM tbl_comb_w_glossary, tbl_list_of_base_word_new where tbl_list_of_base_word_new.list_of_base_word_id=tbl_comb_w_glossary.list_of_base_word_id ORDER BY tbl_list_of_base_word_new.list_of_base_word_name";
    $result1 = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result1)){
        $active=0;
        if($row['list_of_base_word_id']==$list_of_base_word_id){
            $active=1;
        }
        $row['active']=$active;
        $all_comb_glossary[]=$row;
    }
    echo json_encode($all_comb_glossary);
}else if(@$_GET['fieldKey']=='get_words_glossary'){
    $column=$_GET['column'];
    if($column=='word_w_id16'){
        $str="";
        $sql="SELECT prefix FROM tbl_comb_w_glossary WHERE lang_id_prefix=16  AND prefix!='' ORDER BY prefix";
        $result1 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result1)){
            $prefix_glossary[]=$row;
        }
        $sql="SELECT lw.list_of_base_word_id,GROUP_CONCAT(if(lw.word_w_id16!='',lw.word_w_id16,null) SEPARATOR ', ') as word_w_id16 FROM tbl_list_of_words_new as lw INNER JOIN tbl_list_of_base_word_new as bwn ON bwn.list_of_base_word_id=lw.list_of_base_word_id AND lw.word_w_id16!='' GROUP By lw.list_of_base_word_id ORDER BY lw.word_w_id16 ASC";
        $result2 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result2)){
            $strs=substr(strtolower($row['word_w_id16']),0,1);
            if($str!=$strs){
                $row1['list_of_base_word_id']=0;
                $row1['word_w_id16']='';
                array_push($words_glossary,$row1);
            }
            $str=substr(strtolower($row['word_w_id16']),0,1);
            $words_glossary[]=$row;
        }
        $sql="SELECT suffix FROM tbl_comb_w_glossary WHERE lang_id_prefix=16 AND suffix!='' ORDER BY suffix";
        $result3 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result3)){
            $suffix_glossary[]=$row;
        }
    }else if($column=='word_w_id17'){
        $str="";
        $sql="SELECT prefix FROM tbl_comb_w_glossary WHERE lang_id_prefix=17  AND prefix!='' ORDER BY prefix";
        $result1 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result1)){
            $prefix_glossary[]=$row;
        }
        $sql="SELECT lw.list_of_base_word_id,GROUP_CONCAT(if(lw.word_w_id17!='',lw.word_w_id17,null) SEPARATOR ', ') as word_w_id17 FROM tbl_list_of_words_new as lw INNER JOIN tbl_list_of_base_word_new as bwn ON bwn.list_of_base_word_id=lw.list_of_base_word_id AND lw.word_w_id17!='' GROUP By lw.list_of_base_word_id ORDER BY lw.word_w_id17 ASC";
        $result2 = mysqli_query($conn, $sql);
        
        while($row=mysqli_fetch_assoc($result2)){
            $strs=substr(strtolower($row['word_w_id17']),0,1);
            if($str!=$strs){
                $row1['list_of_base_word_id']=0;
                $row1['word_w_id17']='';
                array_push($words_glossary,$row1);
            }
            $str=substr(strtolower($row['word_w_id17']),0,1);
            $words_glossary[]=$row;
        }
        $sql="SELECT suffix FROM tbl_comb_w_glossary WHERE lang_id_prefix=17 AND suffix!='' ORDER BY suffix";
        $result3 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result3)){
            $suffix_glossary[]=$row;
        }
    }else if($column=='word_w_id18'){
        $str="";
        $sql="SELECT prefix FROM tbl_comb_w_glossary WHERE lang_id_prefix=18  AND prefix!='' ORDER BY prefix";
        $result1 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result1)){
            $prefix_glossary[]=$row;
        }
        $sql="SELECT lw.list_of_base_word_id,GROUP_CONCAT(if(lw.word_w_id18!='',lw.word_w_id18,null) SEPARATOR ', ') as word_w_id18 FROM tbl_list_of_words_new as lw INNER JOIN tbl_list_of_base_word_new as bwn ON bwn.list_of_base_word_id=lw.list_of_base_word_id AND lw.word_w_id18!='' GROUP By lw.list_of_base_word_id ORDER BY lw.word_w_id18 ASC";
        $result2 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result2)){
            $strs=substr(strtolower($row['word_w_id18']),0,1);
            if($str!=$strs){
                $row1['list_of_base_word_id']=0;
                $row1['word_w_id18']='';
                array_push($words_glossary,$row1);
            }
            $str=substr(strtolower($row['word_w_id18']),0,1);
            $words_glossary[]=$row;
        }
        $sql="SELECT suffix FROM tbl_comb_w_glossary WHERE lang_id_prefix=18 AND suffix!='' ORDER BY suffix";
        $result3 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result3)){
            $suffix_glossary[]=$row;
        }
    }else if($column=='word_w_id19'){
        $str="";
        $sql="SELECT prefix FROM tbl_comb_w_glossary WHERE lang_id_prefix=19  AND prefix!='' ORDER BY prefix";
        $result1 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result1)){
            $prefix_glossary[]=$row;
        }
        $sql="SELECT lw.list_of_base_word_id,GROUP_CONCAT(if(lw.word_w_id19!='',lw.word_w_id19,null) SEPARATOR ', ') as word_w_id19 FROM tbl_list_of_words_new as lw INNER JOIN tbl_list_of_base_word_new as bwn ON bwn.list_of_base_word_id=lw.list_of_base_word_id AND lw.word_w_id19!='' GROUP By lw.list_of_base_word_id ORDER BY lw.word_w_id19 ASC";
        $result2 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result2)){
            $strs=substr(strtolower($row['word_w_id19']),0,1);
            if($str!=$strs){
                $row1['list_of_base_word_id']=0;
                $row1['word_w_id19']='';
                array_push($words_glossary,$row1);
            }
            $str=substr(strtolower($row['word_w_id19']),0,1);
            $words_glossary[]=$row;
        }
        $sql="SELECT suffix FROM tbl_comb_w_glossary WHERE lang_id_prefix=19 AND suffix!='' ORDER BY suffix";
        $result3 = mysqli_query($conn, $sql);
        while($row=mysqli_fetch_assoc($result3)){
            $suffix_glossary[]=$row;
        }
    }
    $array=array(
        'prefixs'=>$prefix_glossary,
        'words'=>$words_glossary,
        'suffixs'=>$suffix_glossary
    );
    echo json_encode($array);
}else if(@$_GET['fieldKey']=='get_admin_book_contents'){
    $sql="SELECT user_content_id, user_content,user_type FROM `tbl_user_content`  WHERE `user_type` = 'admin' ORDER BY `user_content_id` DESC";
    $result = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result)){
        $admin_book_content[]=$row;
    }
    echo json_encode($admin_book_content);
}else if(@$_GET['fieldKey']=='get_user_book_contents'){
    $sql="SELECT user_content_id, user_content,user_type FROM `tbl_user_content`  WHERE `user_type` = 'user' AND user_id=".$user_id." ORDER BY `user_content_id` DESC";
    $result = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result)){
        $user_book_content[]=$row;
    }
    echo json_encode($user_book_content);
}else if($_GET['fieldKey']=='get_search_words'){
    $bword=$_GET['base_word'];
    $list_of_base_word_id=0;
    $sql ='SELECT list_of_base_word_id,list_of_base_word_name  FROM tbl_list_of_base_word_new WHERE list_of_base_word_name = "'.trim($bword).'" ORDER BY list_of_base_word_name ASC';
	$query = mysqli_query($conn,$sql);		
	if(mysqli_num_rows($query)==1){
		$row=mysqli_fetch_assoc($query);			
		$list_of_base_word_id=$row['list_of_base_word_id'];
	}else{
	    $sql ='SELECT list_of_base_word_id  FROM tbl_list_of_words_new  WHERE word_w_id16  LIKE "'.trim($bword).'%" OR word_w_id17  LIKE "'.trim($bword).'%" OR word_w_id18  LIKE "'.trim($bword).'%" OR word_w_id19  LIKE "'.trim($bword).'%" 	Limit 1';
		$query = mysqli_query($conn, $sql);
 		if(mysqli_num_rows($query)==1){
			$row = mysqli_fetch_assoc($query);		
 			$list_of_base_word_id=$row['list_of_base_word_id'];
        }else{
            $sql ='SELECT list_of_base_word_id  FROM tbl_little_text  WHERE add_word_w_id16  LIKE "%'.trim($bword).'%" Limit 1';
		    $query = mysqli_query($conn, $sql);
            if(mysqli_num_rows($query)==1){
			    $row = mysqli_fetch_assoc($query);		
 			    $list_of_base_word_id=$row['list_of_base_word_id'];
            }
        }
	}
	if($list_of_base_word_id>0){
	    // getting list of baseword details 
	    $sql='SELECT list_of_base_word_id, list_of_base_word_name,reasons,list_out_words FROM `tbl_list_of_base_word_new`  WHERE `list_of_base_word_id` = "'.$list_of_base_word_id.'" ORDER BY list_of_base_word_name ASC';
        $result = mysqli_query($conn, $sql);
        $row=mysqli_fetch_assoc($result);
        $baseWord=$row;
       // print_r($baseWord);
        /* Next Previous*/
		$sql2='SELECT  list_of_base_word_name FROM tbl_list_of_base_word_new WHERE list_of_base_word_name > "'.@$baseWord['list_of_base_word_name'].'" ORDER BY list_of_base_word_name ASC LIMIT 1';
		$query2 = mysqli_query($conn, $sql2);
		$next=mysqli_fetch_assoc($query2); 
		$next=(($next['list_of_base_word_name'])? $next['list_of_base_word_name']:'');
		$sql3='SELECT  list_of_base_word_name FROM tbl_list_of_base_word_new WHERE list_of_base_word_name < "'.@$baseWord['list_of_base_word_name'].'" ORDER BY list_of_base_word_name DESC LIMIT 1';
	 	$query3 = mysqli_query($conn, $sql3);
	 	$prev=mysqli_fetch_assoc($query3); 
	 	$prev=($prev['list_of_base_word_name']?$prev['list_of_base_word_name']:'');
		/* close Next Previous*/
        //left and right basewords
        /**************** Left Side Base words**************************/
	 	$sqll='SELECT list_of_base_word_id,list_of_base_word_name,base_color FROM tbl_list_of_base_word_new WHERE list_of_base_word_name 
	 		< "'.@$baseWord['list_of_base_word_name'].'" ORDER BY list_of_base_word_name DESC ';
		$query = mysqli_query($conn, $sqll);
		$leftstr='';
		while($row=mysqli_fetch_assoc($query)) {
		     $padding_buttom=0;
		    $strs=substr(strtolower($row['list_of_base_word_name']),0,1);
            if($leftstr!=$strs){
                $padding_buttom=1;
            }
		    $row['padding_buttom']=$padding_buttom;
			$leftbasewords[] = $row;
			$leftstr=substr(strtolower($row['list_of_base_word_name']),0,1); 
		}		 	
	 	/**************** Right Side Base words**************************/
	 	$sqlr='SELECT list_of_base_word_id,list_of_base_word_name,base_color FROM tbl_list_of_base_word_new WHERE list_of_base_word_name 
	 		> "'.@$baseWord['list_of_base_word_name'].'" ORDER BY list_of_base_word_name ASC ';
		$query = mysqli_query($conn, $sqlr);
		$rghtstr='';
		while($row=mysqli_fetch_assoc($query)) {
		    $padding_buttom=0;
		    $strs=substr(strtolower($row['list_of_base_word_name']),0,1);
            if($rghtstr!=$strs){
                $padding_buttom=1;
            }
		    $row['padding_buttom']=$padding_buttom;
			$rightbasewords[] = $row;
			$rghtstr=substr(strtolower($row['list_of_base_word_name']),0,1); 
			
		}
		$sqls='SELECT * FROM tbl_indi_w_glossary WHERE list_of_base_word_id='.@$baseWord['list_of_base_word_id'];
		$query = mysqli_query($conn, $sqls);
		$article_determiner=0;
		$preposition=0;
		$interjection=0;
		$pronoun=0;
		$conjuction=0;
		$contraction=0;
		$foreign_word=0;
		$proverb=0;
		$abbreviation=0;
        $acronym=0;
        $symbol=0;
	    while($row=mysqli_fetch_assoc($query)) {
	        if($row['article_determiner']!='' && $article_determiner==0){
	            $match_indv_fire['article_determiner']=1;
	            $article_determiner=1;
	        }
	        if($row['interjection']!='' && $interjection==0){
	            $match_indv_fire['interjection']=1;
	            $interjection=1;
	        }
            if($row['preposition']!='' && $preposition==0){
	            $match_indv_fire['preposition']=1;
	            $preposition=1;
	        }
	        if($row['pronoun']!='' && $pronoun==0){
	            $match_indv_fire['pronoun']=1;
	            $pronoun=1;
	        }
	        if($row['conjuction']!='' && $conjuction==0){
	            $match_indv_fire['conjuction']=1;
	            $conjuction=1;
	        }
	        if($row['contraction']!='' && $contraction==0){
	            $match_indv_fire['contraction']=1;
	            $contraction=1;
	        }
	        if($row['foreign_word']!='' && $foreign_word==0){
	            $match_indv_fire['foreign_word']=1;
	            $foreign_word=1;
	        }
	        if($row['proverb']!='' && $proverb==0){
	            $match_indv_fire['proverb']=1;
	            $proverb=1;
	        }
            if($row['abbreviation']!='' && $abbreviation==0){
	            $match_indv_fire['abbreviation']=1;
	            $abbreviation=1;
	        }
	        if($row['acronym']!='' && $acronym==0){
	            $match_indv_fire['acronym']=1;
	            $acronym=1;
	        }
	        if($row['symbol']!='' && $symbol==0){
                $match_indv_fire['symbol']=1;
                $symbol=1;
	        }
		}
		//Comb matching fire
        $sqls="SELECT * FROM tbl_comb_w_glossary WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' LIMIT 1";
        $query = mysqli_query($conn, $sqls);
        $match_indv_fire['combiningfire']=mysqli_num_rows($query);
        //Comb matching fire
        //affix matching fire
        $sqls="SELECT * FROM tbl_pre_sub_form_new WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' LIMIT 1";
        $query = mysqli_query($conn, $sqls);
        $match_indv_fire['affixfire']=mysqli_num_rows($query);
        
        //llb matching fire
        $sqlllb="SELECT list_of_base_word_name FROM tbl_list_of_base_word_new WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' AND reasons !='' ";
        $query = mysqli_query($conn, $sqlllb);
        $match_indv_fire['llbfire']=mysqli_num_rows($query);
        
		// close Matching Fire;	
		// Word Searching 
		$count_word_type=array();
		$wordType=array();
		$littleText=array();
		//calculte total no of position 
        $sqls="SELECT word_type FROM `tbl_list_of_words_new` WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' GROUP by word_type  ORDER by word_type ASC";
        $queryy = mysqli_query($conn, $sqls);
        $total_word_types=mysqli_num_rows($queryy);
        while($row=mysqli_fetch_assoc($queryy)) {
            $data=$row['word_type'];
            array_push($wordType,$data);
	  	}
        $sql="SELECT additional_position FROM `tbl_little_text` WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' GROUP by additional_position  ORDER by additional_position ASC";
        $result = mysqli_query($conn, $sql);
        $total_little_types=mysqli_num_rows($result);
        while($row=mysqli_fetch_assoc($result)) {
           $val=$row['additional_position'];
           $littleText['key'.$val]=$val;
        }
        for($wt=1;$wt<=($total_word_types+$total_little_types);$wt++){
            if(array_search($wt,$littleText)){
                $data1['type']='lt';
                $data1['serial_order']=$wt;
                array_push($count_word_type,$data1);
            }else {
                $data1['type']='wd';
                $data1['serial_order']= array_shift($wordType); 
                array_push($count_word_type,$data1);
            }
        }
        for($wt=0;$wt<count($count_word_type);$wt++){
	        $count_word_type[$wt]['serial_order'];
	        $data=array();
	        $data['fome_type']=0;
            $data['list_of_words_id']=0;
            $data['list_of_base_word_id']=0;
            $data['comb_status']=0;
            $data['word_type_status']=0;
            $data['word_type']=0;
            $data['pre_sub_form_id']=0;
            $data['pre_form']='';
            $data['sub_form']='';
            $data['pre_form_descp']='';
            $data['sub_form_descp']='';
            $data['word_line']=0;
            $data['match_w_id16']=0;
	  		$data['match_w_id17']=0;
	  		$data['match_w_id18']=0;
	  		$data['match_w_id19']=0;
	  		$data['font_16']=0; 
            $data['font_17']=0;
            $data['font_18']=0;
            $data['font_19']=0;
            $data['word_w_id16']='';
            $data['word_w_id17']='';
            $data['word_w_id18']='';
            $data['word_w_id19']='';
            $data['little_text_id']=0;
            $data['add_word_w_id16']='';
            $data['lt_word']="";
            $data['add_word_desc_id16']='';
            $data['add_word_w_color16']=0;
            $data['additional_position']=0;
            $data['match_lt_w']=0;
            $data['padding_buttom']=0;
            $lt_words="";
            if($count_word_type[$wt]['type']=='wd'){
                $sql="SELECT * FROM `tbl_list_of_words_new` WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' AND word_type=".($count_word_type[$wt]['serial_order']);
                $result = mysqli_query($conn, $sql);
                $i=0;
                while($row=mysqli_fetch_assoc($result)){
                    $line=0;
                    if($i==0){
                        $line=1;
                    }
                    $i++;
                    $data['fome_type']=1;
                    $data['list_of_words_id']=$row['list_of_words_id'];
                    $data['list_of_base_word_id']=$row['list_of_base_word_id'];
                    $data['comb_status']=$row['comb_status'];
                    $data['word_type_status']=$row['word_type_status'];
                    $data['word_type']=$row['word_type'];
                    $data['word_w_id16']=$row['word_w_id16'];
                    $data['word_w_id17']=$row['word_w_id17'];
                    $data['word_w_id18']=$row['word_w_id18'];
                    $data['word_w_id19']=$row['word_w_id19'];
                    $data['font_16']=$row['word_w_mng_status16']; 
                    $data['font_17']=$row['word_w_mng_status17'];
                    $data['font_18']=$row['word_w_mng_status18'];
                    $data['font_19']=$row['word_w_mng_status19'];
                    $data['word_line']=$line;
                    array_push($words,$data);
                	array_push($allwords, $row['word_w_id16']);	  		
        	  		array_push($allwords, $row['word_w_id17']);	  		
        	  		array_push($allwords, $row['word_w_id18']);	  		
        	  		array_push($allwords, $row['word_w_id19']);	
                }
            }
            else if($count_word_type[$wt]['type']=='lt'){
                $sql="SELECT * FROM `tbl_little_text` WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' AND additional_position=".($count_word_type[$wt]['serial_order']);
                $result = mysqli_query($conn, $sql);
                $lt_words="";
                $ltstr="";
                while($row=mysqli_fetch_assoc($result)){
                    $strs=substr(strtolower($row['add_word_w_id16']),0,1); 
                    $padding_buttom=0;
                    if($ltstr!=$strs){
                        $padding_buttom=1;
                    }
                    $arr=explode("-",$row['add_word_w_id16']);
                    $val=$arr[0];
                    if($lt_words==$arr[0]){
                       $val="";
                    }
                    $lt_words=$arr[0];
                    $data['fome_type']=2;
                    $data['little_text_id']=$row['little_text_id'];
                    $data['add_word_w_id16']=$val." - ".$arr[1];
                    $data['add_word_w_color16']=$row['add_word_w_color16'];
                    $data['add_word_desc_id16']=$row['add_word_desc_id16'];
                    $data['additional_position']=$row['additional_position'];
                    $data['lt_word']=$arr[1];
                    $data['padding_buttom']=$padding_buttom;
                    array_push($words,$data);
                    array_push($allwords,$arr[1]);
                    $ltstr=substr(strtolower($row['add_word_w_id16']),0,1); 
                }
                // Combinig Form
                $sql="SELECT * FROM `tbl_pre_sub_form_new` WHERE list_of_base_word_id='".@$baseWord['list_of_base_word_id']."' AND pre_pos=".($count_word_type[$wt]['serial_order']);
                $result = mysqli_query($conn, $sql);
                while($row=mysqli_fetch_assoc($result)){
                    //$user_book_content[]=$row;
                    $data['fome_type']=3;
                    $data['pre_sub_form_id']=$row['pre_sub_form_id'];
                    $data['pre_form']=$row['pre_form'];
                    $data['sub_form']=$row['sub_form'];
                    $data['pre_form_descp']=$row['pre_form_descp'];
                    $data['sub_form_descp']=$row['sub_form_descp'];
                    array_push($words,$data);
                }
            }
            
	    }
        // Close Word Searching 
        /***** Search Word in Contents **********/
        $allwords=array_filter($allwords);	 			
        $allwords=array_unique($allwords);
        $allwords=array_values($allwords);	 
        if($allwords){
            /********* Admin Book *******************/
            $str='';	 			
            $condtion=' ( user_content  LIKE "% '.$allwords[0].' %"';
            for ($i=1; $i <count($allwords) ; $i++) { 
                $condtion.=' OR user_content LIKE "% '.$allwords[$i].' %"';
            }
            $condtion.=" ) AND user_type='admin'";
            $sqls='SELECT user_content FROM tbl_user_content WHERE '.$condtion;
             $query = mysqli_query($conn, $sqls);
            while($row=mysqli_fetch_assoc($query)) {			
            	$str.= html_entity_decode($row['user_content'],ENT_QUOTES);
            	$adminBookcontent[]= $row;
            }
            $admin_book=$str;
            /********* User Book *******************/
            $ustr='';
            $condtion=' ( user_content  LIKE "% '.$allwords[0].' %"';
            for ($i=1; $i <count($allwords) ; $i++) { 
            	$condtion.=' OR user_content LIKE "% '.$allwords[$i].' %"';
            }
            $condtion.=" ) AND user_type='user' ";
            $sqls='SELECT user_content FROM tbl_user_content WHERE '.$condtion.' AND user_id='.$user_id;
            $query = mysqli_query($conn, $sqls);
            while($row=mysqli_fetch_assoc($query)){
            	if($row['user_content']){
            		$ustr.= html_entity_decode($row['user_content'],ENT_QUOTES);
            		$userBookcontent[]= $row;
            	}				
            }
			$user_book=$ustr;
			$ditto='';
			$txt_ditto='';
			for ($i=0; $i <count($words);$i++) { 					
				$word1=$words[$i]['word_w_id16'];
				$word2=$words[$i]['word_w_id17'];
				$word3=$words[$i]['word_w_id18'];
				$word4=$words[$i]['word_w_id19'];
				$word5=$words[$i]['lt_word'];
				if($word1){
					$data=explode("-",$word1);
					$txt_ditto=$data[0];							
					if($ditto!=$data[0]){
						$search=$data[0];						
				   	$pattern = preg_quote($search, '/');
					  $pattern = "/^.*$pattern.*\$/mi";
					  if(preg_match_all($pattern, $str.$ustr)) {
					   	$words[$i]['match_w_id16']=1;
					  }	
					}else{
						$data[0]=" -";
						$words[$i]['word_w_id16']=implode(' ', $data);
					}
					$ditto=$txt_ditto;
				}
				if($word2){
					$search=$word2;						
			   	    $pattern = preg_quote($search, '/');
				    $pattern = "/^.*$pattern.*\$/mi";
					if(preg_match_all($pattern, $str.$ustr)) {
                        $words[$i]['match_w_id17']=1;
					}						
				}
				if($word3){						
                    $search=$word3;						
                    $pattern = preg_quote($search, '/');
                    $pattern = "/^.*$pattern.*\$/mi";
                    if(preg_match_all($pattern, $str.$ustr)) {
                        $words[$i]['match_w_id18']=1;
                    }	
				}
                if($word4){						
                    $search=$word4;						
                    $pattern = preg_quote($search, '/');
                    $pattern = "/^.*$pattern.*\$/mi";
                    if(preg_match_all($pattern, $str.$ustr)) {
                        $words[$i]['match_w_id19']=1;
                    }					  
                }	
                if($word5){						
                    $search=$word5;						
                    $pattern = preg_quote($search, '/');
                    $pattern = "/^.*$pattern.*\$/mi";
                    if(preg_match_all($pattern, $str.$ustr)) {
                        $words[$i]['match_lt_w']=1;
                    }					  
                }		
			}
		}	
		// Combining form
        $sqls='SELECT pre_sub_form_id,pre_form,sub_form,pre_form_descp,sub_form_descp FROM tbl_pre_sub_form_new WHERE pre_pos=0 AND list_of_base_word_id='.@$baseWord['list_of_base_word_id'];
    	$query = mysqli_query($conn, $sqls);
    	while($row=mysqli_fetch_assoc($query)){
    	    $pre_suf_combine_form[]= $row;
        }
		$sqls='SELECT combining_form_id,pre_sub_form_id,list_of_base_word_id,pre_combining_form,sub_combining_form,pre_combining_form_redirection,sub_combining_form_redirection FROM tbl_combining_form_new WHERE list_of_base_word_id='.@$baseWord['list_of_base_word_id'];
		$query = mysqli_query($conn, $sqls);
		while($row=mysqli_fetch_assoc($query)){
		    $combining_form_word[]= $row;
	    }
	    $sqls='SELECT combining_form_word_id,combining_form_id,pre_sub_form_id,list_of_base_word_id,word16,word17,word18,word19,combin_word FROM tbl_combining_form_word WHERE list_of_base_word_id='.@$baseWord['list_of_base_word_id'];
		$query = mysqli_query($conn, $sqls);
		while($row=mysqli_fetch_assoc($query)){
		    $combining_form_list[]= $row;
	    }
		//	pre_suf_combine_form
		/////////////////////////	
   	}
	$all_words=array(
	    'pre_suf_combine_form'=>$pre_suf_combine_form,
	    'combining_form_word'=>$combining_form_word,
	    'combining_form_list'=>$combining_form_list,
		'baseWord'=>$baseWord,
		'nextW'=>$next,
		'prevW'=>$prev,
		'leftbasewords'=>$leftbasewords,
		'rightbasewords'=>$rightbasewords,
		'match_indv_fire'=>$match_indv_fire,
        'adminBookcontent'=>$adminBookcontent,
		'userBookcontent'=>$userBookcontent,
		'words'=>$words,
		'allwords'=>$allwords,
		'admin_book'=>$admin_book,
		'user_book'=>$user_book,
	);
    echo json_encode($all_words);
 }else if(@$_GET['fieldKey']=='get_all_user_descp'){
    $base_word=$_GET['base_word'];
    $wdesc='SELECT word_descp_id,word_description,list_of_base_word_name,user_id FROM tbl_word_descriptions WHERE list_of_base_word_name ="'.$base_word.'" AND  user_id="'.$user_id.'" ORDER BY word_descp_id  DESC';
    $result=mysqli_query($conn,$wdesc);
    while($row=mysqli_fetch_assoc($result)){
        $all_user_descp[]=$row;
    }
    echo json_encode($all_user_descp);
}
else if(@$_GET['fieldKey']=='delete_user_descp'){
    $base_word=$_GET['base_word'];
    $word_descp_id=$_GET['word_descp_id'];
    $dsql = "DELETE FROM tbl_word_descriptions WHERE word_descp_id=".$word_descp_id;
    if (mysqli_query($conn, $dsql)) {
        
        $wdesc='SELECT word_descp_id,word_description,list_of_base_word_name,user_id FROM tbl_word_descriptions WHERE list_of_base_word_name ="'.$base_word.'" AND  user_id="'.$user_id.'" ORDER BY word_descp_id  DESC';
        $result=mysqli_query($conn,$wdesc);
        while($row=mysqli_fetch_assoc($result)){
            $all_user_descp[]=$row;
        }
        echo json_encode($all_user_descp);
    }
}
else if(@$_GET['fieldKey']=='get_little_text_match_words'){
    $words = $_GET['word'];	
    $lwords=array();
    $sql="SELECT little_text_id,add_word_w_id16,add_word_w_color16 FROM `tbl_little_text` WHERE `add_word_w_id16` LIKE '".$words."%' ORDER BY add_word_w_id16 ASC";
    $query = mysqli_query($conn,$sql);				
    while($row = mysqli_fetch_assoc($query)){
        $padding_buttom=0;
        $arr=explode("-",$row['add_word_w_id16']);
        $strng=trim($arr[1]);
        $strs=substr(strtolower($strng),0,1);
            if($str!=$strs){
                $padding_buttom=1;
            }
        $row['padding_buttom']=$padding_buttom;    
        $row['add_word_w_id16']=trim($arr[1]);
    	$lwords[] = $row;		
    	$str=substr(strtolower($strng),0,1); 
    }		
    echo json_encode($lwords);	
}else if(@$_GET['fieldKey']=='getallmatchingcomblistwordform'){
    $words = $_GET['word'];	
    $lwords=array();
    $sql="SELECT combining_form_word_id,word16 FROM `tbl_combining_form_word` WHERE `word16` LIKE '".$words."%' ORDER BY word16 ASC";
    $query = mysqli_query($conn,$sql);				
    while($row = mysqli_fetch_assoc($query)){	
        //word=add_word_w_id16
        $arr=explode("-",$row['word16']);
        $row['word16']=$arr[1];
    	$lwords[] = $row;			
    }		
    echo json_encode($lwords);	
}else if(@$_GET['fieldKey']=='get_admin_highlight_book_contents'){
    $base_word_id = $_GET['base_word_id'];	
    $aallwords=array();
    $sql="SELECT word_w_id16, word_w_id17, word_w_id18,word_w_id19 FROM tbl_list_of_words_new where list_of_base_word_id='".$base_word_id."'";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
    	array_push($aallwords, ltrim($row['word_w_id16']));	  		
      	array_push($aallwords, ltrim($row['word_w_id17']));	  		
      	array_push($aallwords, ltrim($row['word_w_id18']));	  		
      	array_push($aallwords, ltrim($row['word_w_id19']));
    }
    $sql="SELECT add_word_w_id16 FROM `tbl_little_text` WHERE list_of_base_word_id='".$base_word_id."'";
	$query = mysqli_query($conn,$sql);				
	while($row = mysqli_fetch_assoc($query)){
	   $arr=explode("-",$row['add_word_w_id16']);
	   array_push($aallwords,ltrim($arr[1]));	 
	}		
    $aallwords=array_filter($aallwords);	 			
    $aallwords=array_unique($aallwords);
    $aallwords=array_values($aallwords);
    $Array2 = "'" .implode("'|'", $aallwords  ) . "'"; 
    $adminContents=array();
    /// SQL 
    $sqls = array('0'); // Stop errors when $words is empty
    foreach($aallwords as $word){
        $sqls[] = 'user_content LIKE "% '.$word.' %"';
    }
    $sql = 'SELECT  user_content  FROM tbl_user_content  WHERE (  '.implode(" OR ", $sqls) .' ) AND user_type="admin"';
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {  
        while($row = mysqli_fetch_assoc($result)) {
            $adminContents[]=$row['user_content'];  		
        }
    }
    $allword=array();
    $replacewords=array();
    rsort($aallwords);
    for ($i=0; $i <count($aallwords); $i++) { 
        $data=" <span class='highlight_words'>".$aallwords[$i]."</span> ";
        $data1=" ".$aallwords[$i]." ";
        array_push($replacewords,$data);
        array_push($allword,$data1);
    }
    $contentss = str_ireplace($allword, $replacewords, $adminContents);
    $array=array(
        'contentss'=>$contentss,  
    );
	echo json_encode($array);	
}else if(@$_GET['fieldKey']=='get_user_highlight_book_contents'){
    $base_word_id = $_GET['base_word_id'];	
    $aallwords=array();
    $sql="SELECT word_w_id16, word_w_id17, word_w_id18,word_w_id19 FROM tbl_list_of_words_new where list_of_base_word_id='".$base_word_id."'";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
    	array_push($aallwords, ltrim($row['word_w_id16']));	  		
      	array_push($aallwords, ltrim($row['word_w_id17']));	  		
      	array_push($aallwords, ltrim($row['word_w_id18']));	  		
      	array_push($aallwords, ltrim($row['word_w_id19']));
    }
    $sql="SELECT add_word_w_id16 FROM `tbl_little_text` WHERE list_of_base_word_id='".$base_word_id."'";
	$query = mysqli_query($conn,$sql);				
	while($row = mysqli_fetch_assoc($query)){
        $arr=explode("-",$row['add_word_w_id16']);
        array_push($aallwords,ltrim($arr[1]));	 
	}		
    $aallwords=array_filter($aallwords);	 			
    $aallwords=array_unique($aallwords);
    $aallwords=array_values($aallwords);
    $Array2 = "'" .implode("'|'", $aallwords  ) . "'"; 
    $adminContents=array();
    /// SQL 
    $sqls = array('0'); // Stop errors when $words is empty
    foreach($aallwords as $word){
        $sqls[] = 'user_content LIKE "% '.$word.' %"';
    }
    $sql = 'SELECT  user_content  FROM tbl_user_content  WHERE (  '.implode(" OR ", $sqls) .'  ) AND user_type="user" AND user_id='.$user_id;
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {  
        while($row = mysqli_fetch_assoc($result)) {
            $adminContents[]=$row['user_content'];  		
        }
    }
    $allword=array();
    $replacewords=array();
    rsort($aallwords);
    for ($i=0; $i <count($aallwords); $i++) { 
        $data=" <span class='highlight_words'>".$aallwords[$i]."</span> ";
        $data1=" ".$aallwords[$i]." ";
        array_push($replacewords,$data);
        array_push($allword,$data1);
    }
    $contentss = str_ireplace($allword, $replacewords, $adminContents);
    $array=array(
        'contentss'=>$contentss,  
    );
	echo json_encode($array);	
}
else if(@$_GET['fieldKey']=='getwordmeanings'){
    $field=$_GET['column'];
    $list_of_words_id=$_GET['list_of_words_id'];
    $meanings="";
    if($field=='word_w_id16'){$meanings=" word_w_mng_id16 as meanings  ";}
    else if($field=='word_w_id17'){$meanings=" word_w_mng_id17 as meanings  ";}
    else if($field=='word_w_id18'){$meanings=" word_w_mng_id18 as meanings  ";}
    else if($field=='word_w_id19'){$meanings=" word_w_mng_id19 as meanings  ";}
    $condtion="SELECT ".$meanings."  FROM `tbl_list_of_words_new` WHERE list_of_words_id=".$list_of_words_id;
	$query = mysqli_query($conn,$condtion);
	$rec=mysqli_fetch_assoc($query) ;
    echo json_encode($rec);
}
////////////// Word Type /////////////////
else if(@$_GET['fieldKey']=='getallmatchingword'){
    $words=str_replace("*","&",$_GET['word']);	
    $field=$_GET['column'];
    $fonts="";
    if($field=='word_w_id16'){$fonts=" word_w_mng_status16 as fonts , ";}
    else if($field=='word_w_id17'){$fonts=" word_w_mng_status17 as fonts , ";}
    else if($field=='word_w_id18'){$fonts=" word_w_mng_status18 as fonts , ";}
    else if($field=='word_w_id19'){$fonts=" word_w_mng_status19 as fonts , ";}
    $list='';	$condition1='';	$binry="";
    $like=" like ";
    $sc="'";
    $wrd=$wrd1="";
    $singlechar="";
    $singlechar1="";
    $wrdlength=strlen($words);
    $the_string=substr($words,0,4);
    $a_string=substr($words,0,2);
    $my_string=substr($words,0,3);
    $in_string=substr($words,-6);
	$pos= strrpos($words,"'");
	if($wrdlength==1 && ctype_upper($words)){
		$col='';	  			
		$col=$field.' REGEXP BINARY "[A-Z]" AND ';	  			 			 
		$words='';  
		$like="";		
		$singlechar=$col." LENGTH( ";
		$singlechar1=" ) = 1 ";
	}else if($a_string=='A ' ){
		$condition1.=' BINARY "'.$a_string.'%" ';	 	
	}else if($wrdlength==1 && ctype_lower($words)){
		$col='';	  			
		$col=$field.' REGEXP BINARY "[a-z]" AND ';	  			 			 
		$words='';  
		$like="";		
		$singlechar=$col." LENGTH( ";
		$singlechar1=" ) = 1 ";
	}else if($a_string=='a '){
	    $condition1.='BINARY "'.$a_string.'%" ';
    } else if($wrd1=='lly' && $field=='word_w_id19'){
        $words=$wrd1;
        $condition1.='"%'.$words.'" ';
    }else if($wrd=='ly' && $field=='word_w_id19'){
        $words=$wrd;
        $condition1.='"%'.$words.'" AND '.$field.' NOT LIKE "%lly" ';
    }else if(strtolower($the_string)=='the '){
        $condition1.='"'.$the_string.'%"';	  		 
    }else if(strtolower($my_string)=='my '){
        $condition1.='"'.$my_string.'%"';	  		 
    }else if(strtolower($in_string)=='( in )' && $field=='word_w_id18'){
        $condition1.='"%'.$in_string.'"';
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';	  			
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
	}else if(preg_match("/^[A-Z]/",$words)){
		$binry=" BINARY ";$like=" ";$apps="'";
		$condition1.="REGEXP '^[A-Z]' ";
		$condition1.=' AND '.$field.' NOT LIKE "the %" ';
		$condition1.=' AND '.$field.' NOT LIKE "% %" ';
		$condition1.=' AND '.$field.' NOT LIKE "%'.$sc.'%" ';	
	 	$condition1.=' AND '.$field.' NOT LIKE "a %" ';
	 	$condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
		$condition1.=' AND '.$field.' NOT LIKE "%-%" ';		 
  		$condition1.=' AND LENGTH('.$field.') !=1';
    }else if (count(explode("&",$words))>1){
        $condition1.='"%&%" ';
    }else if (strpos(strtolower($words), ' or ')){
        $condition1.='"% or %" ';
    }else if (count(explode(" ",$words))>2){
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*[[:space:]][a-z]*[[:space:]][a-z]*$')";	
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";		 
    }else if (count(explode(" ",$words))>1){	  			
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*[[:space:]][a-z]*$')";
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	
    }else if (count(explode("-",$words))>2 ){
    	$like=" ";
     	$condition1.="REGEXP ('^[a-z]*-[a-z]*-[a-z]*$')";
     	$condition1.=' AND '.$field.' NOT LIKE "the %" ';		
     	$condition1.=' AND '.$field.' NOT LIKE "a %" ';
     	$condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
     	$condition1.=' AND '.$field.' NOT LIKE "%&%" ';
    	$condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	 
    }else if (count(explode("-",$words))>1 ){	  			
    	$like=" ";
    	$condition1.="REGEXP ('^[a-z]*-[a-z]*$')";
    	$condition1.=' AND '.$field.' NOT LIKE "the %" ';
    	$condition1.=' AND '.$field.' NOT LIKE "a %" ';
     	$condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
     	$condition1.=' AND '.$field.' NOT LIKE "%&%" ';
    	$condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	
    }else if($pos){
    	$words=substr($words,$pos);
    	$condition1.='"%'.$words.'"';
    	$condition1.=' AND '.$field.' NOT LIKE "the %" ';
		$condition1.=' AND '.$field.' NOT LIKE "a %" ';
	 	$condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
	 	$condition1.=' AND '.$field.' NOT LIKE "%&%" ';
		$condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";
	}else{		
		$words=substr($words,-3);
		$condition1.='"%'.$words.'" AND '.$field.' NOT LIKE "the %" ';
		$condition1.=' AND '.$field.' NOT LIKE "%-%" ';
		$condition1.=' AND '.$field.' NOT LIKE "% %" ';
		$condition1.=' AND '.$field.' NOT LIKE "a %" ';
		$condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
		$condition1.=" AND BINARY ".$field." REGEXP '^[a-z]' ";
	}
	$rec= array();
	if($words || $wrdlength==1){
		$padding_wl1=$wrdltr1='';
		$word_count1=0;
		$condtion="SELECT ".$fonts." GROUP_CONCAT(if(".$field."!='',".$field.",null)	SEPARATOR ', ') as filed FROM `tbl_list_of_words_new` WHERE ".$binry." ".$singlechar.$field.$singlechar1.$like;
		$condtion.=$condition1.' Group By list_of_base_word_id ORDER BY ';
		$condtion.=$field." ASC";	
		$query = mysqli_query($conn,$condtion);
		$str="";
		while($row=mysqli_fetch_assoc($query)) {
		    $padding_buttom=0;
		    $strs=substr(strtolower($row['filed']),0,1);
            if($str!=$strs){
                $padding_buttom=1;
            }
		    $row['padding_buttom']=$padding_buttom;
			$rec[]=$row;
			$str=substr(strtolower($row['filed']),0,1); 
		}
	}
    echo json_encode($rec);
}
//////////// Combine word List ///////////////////
else if(@$_GET['fieldKey']=='getallmatchingcomblistword'){
    $words=str_replace("*","&",$_GET['word']);	
    $field=$_GET['column'];
    $list='';	$condition1='';	$binry="";
    $like=" like ";
    $sc="'";
    $wrd=$wrd1="";
    $singlechar="";
    $singlechar1="";
    $wrdlength=strlen($words);
    $the_string=substr($words,0,4);
    $a_string=substr($words,0,2);
    $my_string=substr($words,0,3);
    $in_string=substr($words,-6);
    $pos= strrpos($words,"'");
    if($wrdlength==1 && ctype_upper($words)){
    	$col='';	  			
    	$col=$field.' REGEXP BINARY "[A-Z]" AND ';	  			 			 
    	$words='';  
    	$like="";		
    	$singlechar=$col." LENGTH( ";
    	$singlechar1=" ) = 1 ";
    }else if($a_string=='A ' ){
    	$condition1.=' BINARY "'.$a_string.'%" ';	 	
    }
    else if($wrdlength==1 && ctype_lower($words)){
    	$col='';	  			
    	$col=$field.' REGEXP BINARY "[a-z]" AND ';	  			 			 
    	$words='';  
        $like="";		
        $singlechar=$col." LENGTH( ";
        $singlechar1=" ) = 1 ";
    }else if($a_string=='a '){
        $condition1.='BINARY "'.$a_string.'%" ';
    }else if($wrd1=='lly' && $field=='word19'){
        $words=$wrd1;
        $condition1.='"%'.$words.'" ';
    }else if($wrd=='ly' && $field=='word19'){
        $words=$wrd;
        $condition1.='"%'.$words.'" AND '.$field.' NOT LIKE "%lly" ';
    }else if(strtolower($the_string)=='the '){
        $condition1.='"'.$the_string.'%"';	  		 
    }else if(strtolower($my_string)=='my '){
        $condition1.='"'.$my_string.'%"';	  		 
    }else if(strtolower($in_string)=='( in )' && $field=='word18'){
        $condition1.='"%'.$in_string.'"';
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';	  			
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
    }else if(preg_match("/^[A-Z]/",$words)){
        $binry=" BINARY ";$like=" ";$apps="'";
        $condition1.="REGEXP '^[A-Z]' ";
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "% %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%'.$sc.'%" ';	
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%-%" ';		 
        $condition1.=' AND LENGTH('.$field.') !=1';
    }else if (count(explode("&",$words))>1){
        $condition1.='"%&%" ';
    }else if (strpos(strtolower($words), ' or ')){
        $condition1.='"% or %" ';
    }else if (count(explode(" ",$words))>2){
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*[[:space:]][a-z]*[[:space:]][a-z]*$')";	
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";		 
    }else if (count(explode(" ",$words))>1){	  			
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*[[:space:]][a-z]*$')";
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	
    }else if (count(explode("-",$words))>2 ){
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*-[a-z]*-[a-z]*$')";
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';		
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	 
    }else if (count(explode("-",$words))>1 ){	  			
        $like=" ";
        $condition1.="REGEXP ('^[a-z]*-[a-z]*$')";
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";	
    }else if($pos){
        $words=substr($words,$pos);
        $condition1.='"%'.$words.'"';
        $condition1.=' AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=' AND '.$field.' NOT LIKE "%&%" ';
        $condition1.=" AND BINARY ".$field."  NOT REGEXP '^[A-Z]' ";
    }else{		
        $words=substr($words,-3);
        $condition1.='"%'.$words.'" AND '.$field.' NOT LIKE "the %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%-%" ';
        $condition1.=' AND '.$field.' NOT LIKE "% %" ';
        $condition1.=' AND '.$field.' NOT LIKE "a %" ';
        $condition1.=' AND '.$field.' NOT LIKE "%( in )" ';
        $condition1.=" AND BINARY ".$field." REGEXP '^[a-z]' ";
    }
    $rec= array();
    if($words || $wrdlength==1){
        $padding_wl1=$wrdltr1='';
        $word_count1=0;
        $condtion="SELECT  GROUP_CONCAT(if(".$field."!='',".$field.",null)	SEPARATOR ', ') as filed FROM `tbl_combining_form_word` WHERE ".$binry." ".$singlechar.$field.$singlechar1.$like;
        $condtion.=$condition1.' Group By list_of_base_word_id ORDER BY ';
        $condtion.=$field." ASC";	
        $query = mysqli_query($conn,$condtion);
        $str="";
        while($row=mysqli_fetch_assoc($query)) {
            $padding_buttom=0;
		    $strs=substr(strtolower($row['filed']),0,1);
            if($str!=$strs){
                $padding_buttom=1;
            }
		    $row['padding_buttom']=$padding_buttom;
            $rec[]=$row;
            $str=substr(strtolower($row['filed']),0,1); 
        }
    }
    //	return $rec;
    echo json_encode($rec);
}
else if(@$_GET['fieldKey']=='get_affix_glossary'){
    $list_of_base_word_id=$_GET['list_of_base_word_id'];
    $sql="SELECT * FROM tbl_pre_sub_form_new  ORDER BY pre_form ASC";
    $result1 = mysqli_query($conn, $sql);
    while($row=mysqli_fetch_assoc($result1)){
        $active=0;
        if($row['list_of_base_word_id']==$list_of_base_word_id){
            $active=1;
        }
        $row['active']=$active;
        $all_affix_glossary[]=$row;
    }
    echo json_encode($all_affix_glossary);
}
else if(@$_GET['fieldKey']=='get_count_basewords'){
    $sql = mysqli_query($conn,"SELECT LEFT(word_list,1) as letter , COUNT(word_list) as total_words FROM `tbl_list_of_base_word_new` GROUP BY LEFT(word_list,1)");
    while($row=mysqli_fetch_assoc($sql)) {
        $lettera=strtolower($row['letter']);
        $latters[$lettera] = $row['total_words'];
    }
    $totalwords=mysqli_query($conn,"SELECT list_of_base_word_id FROM tbl_list_of_base_word_new");
    $total=mysqli_num_rows($totalwords);
    
    $letter=@$_GET['aletters'];
    // left Side Baar 
    
    $schars=array();		
    $sql="SELECT tb.list_of_base_word_id, LEFT(tb.word_list,2) as word_list , count(tb.word_list) as total FROM `tbl_list_of_base_word_new` as tb WHERE tb.`word_list` LIKE '".$letter."%' GROUP BY LEFT(tb.word_list,2) ORDER BY tb.word_list ASC";
	$query = mysqli_query($conn, $sql);	
	while($row=mysqli_fetch_assoc($query)) {
		$schars[strtolower($row['word_list'])]=$row['total'];	
		//$no_sidechars[]=$row;
	};
	$schar['word_list']=$letter;
	$countltr=((@$schars[$letter])? @$schars[$letter]: 0);			
	$schar['total_letters']=$countltr;	
	array_push($no_sidechars,$schar);
	for($i = 97 ; $i<=122; $i++) {
		$sltrs=0;$letters=$letter.chr($i);
		if(@$schars[$letters]){$sltrs=$schars[$letters];} 		
		$schar['word_list']=$letters;
		$schar['total_letters']=$sltrs;
		array_push($no_sidechars,$schar);
	}
//	$no_sidechars;
		
    // close Left Side baaar
    
    //============================================
        
		$meaning1=$meaning2=$meaning3=$meaning4=0;
		$sql="SELECT list_of_base_word_id, word_list , number_of_meanings FROM tbl_list_of_base_word_new WHERE word_list LIKE '".$letter."%' ORDER BY  word_list ASC";
		$mngquery = mysqli_query($conn,$sql);
		$heading1='';	$heading2='';	$heading3='';	$heading4='';	
		$prefix1='';	$prefix2='';$prefix3='';	$prefix4='';
		while($row=mysqli_fetch_assoc($mngquery)) {
			$word1=$word2=$word3=$word4='';
			$pref1='';$pref2='';$pref3='';$pref4='';
			if($row['number_of_meanings']==1){
				$head1=substr(strtolower($row['word_list']),0,2);
				if($heading1!=$head1){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$head1;//."-";
					$meaning['meaning2']=$word2;
					$meaning['meaning3']=$word3;
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre1=substr(strtolower($row['word_list']),0,3);				
			//if($prefix1!=$pre1){$pref1=$pre1."-";}
			if($prefix1!=$pre1){$pref1=$pre1;}
				$meaning1++;			 	
			 	$word1=strtolower($row['word_list']);			 
				$heading1=$head1;
				$prefix1=$pre1;
			}
			else if($row['number_of_meanings']==2){
				$head2=substr(strtolower($row['word_list']),0,2);
				if($heading2!=$head2){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$head2;//." - ";					
					$meaning['meaning3']=$word3;
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre2=substr(strtolower($row['word_list']),0,3);				
			//	if($prefix2!=$pre2){$pref2=$pre2." - ";}
				if($prefix2!=$pre2){$pref2=$pre2;}
				$meaning2++;			 	
			 	$word2=strtolower($row['word_list']);			 
				$heading2=$head2;
				$prefix2=$pre2;
			}
			else if($row['number_of_meanings']==3){
				$head3=substr(strtolower($row['word_list']),0,2);
				if($heading3!=$head3){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$word2;	
					$meaning['meaning3']=$head3;//." - ";					
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre3=substr(strtolower($row['word_list']),0,3);				
				//if($prefix3!=$pre3){$pref3=$pre3." - ";}
				if($prefix3!=$pre3){$pref3=$pre3;}
				$meaning3++;			 	
			 	$word3=strtolower($row['word_list']);			 
				$heading3=$head3;
				$prefix3=$pre3;
			}
			else if($row['number_of_meanings']==4){
				$head4=substr(strtolower($row['word_list']),0,2);
				if($heading4!=$head4){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$word2;	
					$meaning['meaning3']=$word3;	
					$meaning['meaning4']=$head4;//." - ";					
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre4=substr(strtolower($row['word_list']),0,3);				
			//	if($prefix4!=$pre4){$pref4=$pre4." - ";}
				if($prefix4!=$pre4){$pref4=$pre4;}
				$meaning4++;			 	
			 	$word4=strtolower($row['word_list']);			 
				$heading4=$head4;
				$prefix4=$pre4;
			}
			$meaning['prefix1']=$pref1;
			$meaning['prefix2']=$pref2;
			$meaning['prefix3']=$pref3;
			$meaning['prefix4']=$pref4;
			$meaning['list_of_base_word_id']=$row['list_of_base_word_id'];
			$meaning['heading']=0;
			$meaning['meaning1']=$word1;
			$meaning['meaning2']=$word2;
			$meaning['meaning3']=$word3;
			$meaning['meaning4']=$word4;
			$meaning['dash']='-';
			array_push($meanings,$meaning);
		}
		$no_meanings = array(
		  array("meaning"=>$meaning1,
		  	"meaning_type"=>'1',	  	
		  ),array("meaning"=>$meaning2,
		  	"meaning_type"=>'2',	  	
		  ),array("meaning"=>$meaning3,
		  	"meaning_type"=>'3',	  	
		  ),array("meaning"=>$meaning4,
		  	"meaning_type"=>'N',	  	
		  ),
	  );
	  //$array=array('meaningwords'=>$meanings ,'no_meanings'=>$no_meanings );
	  
    
    //================================================
    
    $meanings=array(
        'totalwords'=>$total,
        'alpha_letter'=>$latters,
        'no_sidechars'=>$no_sidechars,
        'no_meanings'=>$no_meanings,
        'meaningwords'=>$meanings
        
        );
    echo json_encode($meanings);
}

//==============
else if(@$_GET['fieldKey']=='meaning_word'){
	$list_of_base_word_id=$_GET['list_of_base_word_id'];
	$sql="SELECT * FROM `tbl_list_of_words_new` WHERE `list_of_base_word_id` =".$list_of_base_word_id." GROUP by word_type ORDER BY word_w_id16,word_w_id17,word_w_id18,word_w_id19 ASC";
	$query = mysqli_query($conn, $sql);	
	while($row=mysqli_fetch_assoc($query)) {
		$word='';$fonts=0;
		if($row['word_w_id16']){
			$word=$row['word_w_id16'];
			$fonts=$row['word_w_mng_status16'];
		}else if($row['word_w_id17']){
			$word=$row['word_w_id17'];
			$fonts=$row['word_w_mng_status17'];
		}else if($row['word_w_id18']){
			$word=$row['word_w_id18'];
			$fonts=$row['word_w_mng_status18'];
		}else if($row['word_w_id19']){
			$word=$row['word_w_id19'];
			$fonts=$row['word_w_mng_status19'];
		}
		$mwords['word']=$word;
		$mwords['fonts']=$fonts;
		array_push($matching_meanings,$mwords);	
	};
	 echo json_encode($matching_meanings);
}
else if(@$_GET['fieldKey']=='get_meaning_word'){
    $letter=$_GET['aletters'];
    $condtion=" LIKE '".$letter."%' ";
	$ltr=strlen($letter);
	if($ltr==1){		
		$condtion=" = '".$letter."' ";
	}
	
    
    $meaning1=$meaning2=$meaning3=$meaning4=0;
		$sql="SELECT list_of_base_word_id, word_list , number_of_meanings FROM tbl_list_of_base_word_new WHERE word_list ".$condtion." ORDER BY  word_list ASC";
		$mngquery = mysqli_query($conn,$sql);
		$heading1='';	$heading2='';	$heading3='';	$heading4='';	
		$prefix1='';	$prefix2='';$prefix3='';	$prefix4='';
		while($row=mysqli_fetch_assoc($mngquery)) {
			$word1=$word2=$word3=$word4='';
			$pref1='';$pref2='';$pref3='';$pref4='';
			if($row['number_of_meanings']==1){
				$head1=substr(strtolower($row['word_list']),0,2);
				if($heading1!=$head1){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$head1;//." - ";
					$meaning['meaning2']=$word2;
					$meaning['meaning3']=$word3;
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre1=substr(strtolower($row['word_list']),0,3);				
				//if($prefix1!=$pre1){$pref1=$pre1." - ";}
				if($prefix1!=$pre1){$pref1=$pre1;}
				$meaning1++;			 	
			 	$word1=strtolower($row['word_list']);			 
				$heading1=$head1;
				$prefix1=$pre1;
			}
			else if($row['number_of_meanings']==2){
				$head2=substr(strtolower($row['word_list']),0,2);
				if($heading2!=$head2){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$head2;//." - ";					
					$meaning['meaning3']=$word3;
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre2=substr(strtolower($row['word_list']),0,3);				
				//if($prefix2!=$pre2){$pref2=$pre2." - ";}
				if($prefix2!=$pre2){$pref2=$pre2;}
				$meaning2++;			 	
			 	$word2=strtolower($row['word_list']);			 
				$heading2=$head2;
				$prefix2=$pre2;
			}
			else if($row['number_of_meanings']==3){
				$head3=substr(strtolower($row['word_list']),0,2);
				if($heading3!=$head3){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$word2;	
					$meaning['meaning3']=$head3;//." - ";					
					$meaning['meaning4']=$word4;
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre3=substr(strtolower($row['word_list']),0,3);				
				//if($prefix3!=$pre3){$pref3=$pre3." - ";}
				if($prefix3!=$pre3){$pref3=$pre3;}
				$meaning3++;			 	
			 	$word3=strtolower($row['word_list']);			 
				$heading3=$head3;
				$prefix3=$pre3;
			}
			else if($row['number_of_meanings']==4){
				$head4=substr(strtolower($row['word_list']),0,2);
				if($heading4!=$head4){
					$meaning['list_of_base_word_id']=0;
					$meaning['heading']=1;
					$meaning['meaning1']=$word1;
					$meaning['meaning2']=$word2;	
					$meaning['meaning3']=$word3;	
					$meaning['meaning4']=$head4;//." - ";					
					$meaning['prefix1']=$pref1;
					$meaning['prefix2']=$pref2;
					$meaning['prefix3']=$pref3;
					$meaning['prefix4']=$pref4;
					$meaning['dash']='-';
					array_push($meanings,$meaning);
				}
				$pre4=substr(strtolower($row['word_list']),0,3);				
			//	if($prefix4!=$pre4){$pref4=$pre4." - ";}
				if($prefix4!=$pre4){$pref4=$pre4;}
				$meaning4++;			 	
			 	$word4=strtolower($row['word_list']);			 
				$heading4=$head4;
				$prefix4=$pre4;
			}
			$meaning['prefix1']=$pref1;
			$meaning['prefix2']=$pref2;
			$meaning['prefix3']=$pref3;
			$meaning['prefix4']=$pref4;
			$meaning['list_of_base_word_id']=$row['list_of_base_word_id'];
			$meaning['heading']=0;
			$meaning['meaning1']=$word1;
			$meaning['meaning2']=$word2;
			$meaning['meaning3']=$word3;
			$meaning['meaning4']=$word4;
			$meaning['dash']='-';
			array_push($meanings,$meaning);
		}
		$no_meanings = array(
		  array("meaning"=>$meaning1,
		  	"meaning_type"=>'1',	  	
		  ),array("meaning"=>$meaning2,
		  	"meaning_type"=>'2',	  	
		  ),array("meaning"=>$meaning3,
		  	"meaning_type"=>'3',	  	
		  ),array("meaning"=>$meaning4,
		  	"meaning_type"=>'N',	  	
		  ),
	  );
	$array=array('meaningwords'=>$meanings ,'no_meanings'=>$no_meanings );

	 echo json_encode($array);
}
//============
?>