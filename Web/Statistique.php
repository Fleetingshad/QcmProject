
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
		 <link rel="stylesheet" href="Resultat.css" />
        <link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow" rel="stylesheet">
        <title></title>
    </head>
    <body> 


        
        <div id="desk-nav">
  <nav>
    <ul>
      <li><a href="AccueilR.php">Home</a></li>
      <li><a href="ProfilR.php">Profil</a></li>
      <li><a href="ChoixRD.php">QCM</a></li>
      <li><a href="Index.php">Déconnexion</a></li>
    </ul>
  </nav>
</div>
        
        
        
        
        
<?php require_once('Connexionbdd.php'); ?>

<div class="container">
    <h1>Résultat</h1>
    
    <?php 
	 include('EviteMessageFormulaire.php');

if(isset($_POST['qcm'])and trim($_POST['qcm'])){
try{

require_once('Connexionbdd.php');
$date=time().'</br>';
$tempspasse=$date-$_POST['temps'];
$score=0;$faux=0;$vrai=0;
if(isset($_POST['reponse'])and trim($_POST['reponse']!=' ')){
	if(isset($_POST['checkboxes'])and trim($_POST['checkboxes']!=' ')){
		echo '<h2>Vos réponses au QCM n° '.$_POST['qcm'].' : </h2> ';
        
        $question=$bdd->prepare('Select * from qcm natural join qcm_question natural join question where id_qcm=:idqcm');//pour chaque question
		$question->bindValue(':idqcm',$_POST['qcm']);
		$question->execute();
		while($quest=$question->fetch(PDO :: FETCH_ASSOC)){
        
            echo "<div class=\"form-group\"><label class=\"control-label\" for=\"select\">";
			echo 'Question : '.$quest['question'];		//affichage question
            echo "</label><i class=\"bar\"></i></div> ";
            
              $idquestionn=$quest['id_question'];
			$reponse=$bdd->prepare('Select * from reponse INNER JOIN qcm_question ON reponse.id_question = qcm_question.id_question WHERE qcm_question.id_question=:idquestion and qcm_question.id_qcm=:idqcm;');//pour chaque réponse de la question
			$reponse->bindValue(':idquestion',$idquestionn);
            $reponse->bindValue(':idqcm',$_POST['qcm']);
			$reponse->execute();
			while($rep=$reponse->fetch(PDO::FETCH_ASSOC)){
				foreach($_POST['reponse'] as $c=>$v){
					
					
					if($rep['id_reponse']==$v){
                    
        
         echo'<i class="helper"></i><i class="helper"></i></br> Votre réponse: '.htmlspecialchars($rep['reponse'],ENT_QUOTES);
       
        
                    if ($rep['correct']){
                        
         echo'<i class="helper"><div class="green">Réponse juste</i></div> ';
        
                           $vrai+=1;
							}else{
								echo'<i class="helper"><div class="red">Réponse fausse</i></div> </br>';	//si la réponse est fausse
								echo 'La réponse juste était :' ;
								$repjuste=$bdd->prepare('Select * from reponse INNER JOIN question ON reponse.id_question = question.id_question INNER JOIN public.qcm_question ON question.id_question = qcm_question.id_question WHERE qcm_question.id_question = :mq and qcm_question.id_qcm = :idqcm and reponse.correct=TRUE');//trouve la réponse juste
								$repjuste->bindValue(':mq',$idquestionn);
                                $repjuste->bindValue(':idqcm',$_POST['qcm']);
								$repjuste->execute();
								while($l=$repjuste->fetch(PDO :: FETCH_ASSOC)){
									echo' '.htmlspecialchars($l['reponse'],ENT_QUOTES);//affichage réponse juste
								}
								$faux=1;
							}
					}
				}
			}
			if($faux==0 && $vrai>=1){//faire peut-etre une requete sur le vrai pour savoir combien de reponses justes il y a
				$point=$bdd->prepare('Select * from question where id_question=:mq');
								$point->bindValue(':mq',$idquestionn);
								$point->execute();
								while($ligne=$point->fetch(PDO::FETCH_ASSOC)){
										$score+=$ligne['valeur'];
										echo '</br>Score : + '.$ligne['valeur'].'</br>';
								}
			}
			$faux=0;
			$vrai=0;
		}
	echo'</br></br></br>';
	
	$time=0;
	$tempsdepasse=$bdd->prepare('Select * FROM public.qcm_question natural join public.question where id_qcm=:idqcm');
	$tempsdepasse->bindValue(':idqcm',$_POST['qcm']);
	$tempsdepasse->execute();
	$t=0;
	while($l=$tempsdepasse->fetch(PDO::FETCH_ASSOC)){
		$t+=$l['temps'];
	}
	if($tempspasse>$t){
		$score-=1;
		echo 'Vous avez dépassé le temps (score - 1) : '.$tempspasse.' secondes. </br>';
	}
	if($score<0){
		$score=0;
	}if ($tempspasse<=$t){
        
	echo 'Temps passé : '.$tempspasse.' secondes.</br>';
	}
	echo ' Score : '.$score.'</br></br>';
	}
}

$tim=0;
$ins=$bdd->prepare('select * from repondeur where nom_repondeur=:n');
$ins->bindValue(':n',$_SESSION['user']);
$ins->execute();
while($lu=$ins->fetch(PDO::FETCH_ASSOC)){
		$tim=$lu['id_repondeur'];
		}


$dom=0;$s_dom=0;
$d_sd=$bdd->prepare('SELECT distinct domaine,sous_domaine FROM qcm WHERE id_qcm = :id_qcm');
$d_sd->bindValue(':id_qcm',$_POST['qcm']);
$d_sd->execute();
	while($l=$d_sd->fetch(PDO::FETCH_ASSOC)){
		$dom=$l['domaine'];
		$s_dom=$l['sous_domaine'];
	}


$d='seconds';
$inserer=$bdd->prepare('insert into public.recap_repondeur (id_repondeur,id_qcm,domaine,sous_domaine,date_qcm_fait,note_qcm,temps_qcm) values(:id_rep,:id_qcm,:dom,:s_dom,date_trunc(:mot,now()),:note,:tempspasse)');
$inserer->bindValue(':id_rep',$tim);
$inserer->bindValue(':id_qcm',$_POST['qcm']);
$inserer->bindValue(':dom',$dom);
$inserer->bindValue(':s_dom',$s_dom);
$inserer->bindValue(':mot',$d);
$inserer->bindValue(':note',$score);
$inserer->bindValue(':tempspasse',$tempspasse);
$inserer->execute();
}

catch(PDOException $e){
	die('<p>Votre requête est erronée.</p>'.$e);
}
	
	 echo '<div class="button-container">
    <a href="ProfilR.php"><button class="button" type="submit"><span>Profil</span></button></a>
    <a href="ChoixRD.php"><button class="button" type="submit"><span>Refaire un QCM</span></button></a></div>';
    
     echo "</form>";
}else{
	echo 'Tous vos résultats se trouvent sur votre profil.';
	 echo '<div class="button-container">
    <a href="ProfilR.php"><button class="button" type="submit"><span>Profil</span></button></a></div>';
	
}
?>


  
</div>
    
    </body>
	</html>
            
            
            