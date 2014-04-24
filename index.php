<?php
	/* 
	* Page index.php
	* Page d'accueil du site CreaForm
	* Présentation du site
	* Possibilité d'accéder à un formulaire en passant en GET id=X
	* X étant l'id du formulaire demandé
	*/

	/* Inclusion de la librairie ZebraForm */
	require 'Zebraform/Zebra_Form.php';

	/* Initialisation de la session ... */ 
	session_start(); 

	/* Paramètres de Connexion à la base de données */
	$connectionstring = 'mysql:dbname=creationform;host=localhost';
	$user = 'root';
	$password = '';

	/* Connexion à la base de données */
	try {
	    $db = new PDO($connectionstring, $user, $password);
	} catch (PDOException $e) {
	    echo "<p> Base de données inaccessible... =( <br /> Veuillez réessayez plus tard ! </p>";
	}
?>


<!DOCTYPE html>
<html>
    <head>
        <title> CreaForm By Harmo </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
        <link rel="stylesheet" href="Zebraform/styles/zebra_form.css">
        <script src="jquery.js"></script>
        <script src="Zebraform/zebra_form.js"></script>
 		 <script type="text/javascript">
		    function mycallback(valid, label) {
		    	if (valid) {
		        	$('#'+label+'').parent().show();
		    	}
		    	else $('#'+label+'').parent().hide();
		    }
		</script>
		<style>
			body {
				width: 800px;
				margin: auto;
			}

			body h1 {
				text-align: center;
			}

			body p {
				text-align: center;
			}
		</style>
    </head>

    <body>
		<?php
		/* Récupération de l'id demandé s'il existe */
		if(isset($_GET['id'])) {
			$id = (int)$_GET['id']; 
		}
		else  {
			/* Pas d'id demandé, page d'accueil du site */
			echo '<h1> Creaform By Harmo </h1>';
			echo '<p> CreaForm est un utilitaire semblable à Google Forms qui permet de créer un formulaire complet et fonctionnel. Les résultats seront accessible uniquement pour le créateur du formulaire à tout moment. </p>';
			echo '<p> CreaForm est codé en PHP par Harmo grâce à la Librairie Zebra_Form, initialement en Avril 2013 et refondu en 2014.</p>';
			echo '<p> <br /><br />Pour accéder à un formulaire, il vous faut le lien direct de celui-ci.</p>';
			echo '<p> <br /><br />Pour créer un formulaire, cliquez sur le lien ci-dessous. </p>';
			echo "<p> <a href='create.php'> Créer un Formulaire </a> </p>";

		}

		// Si on a un ID et qu'il est différent de zéro (on a déjà casté en int avant)
		if((isset($id))&&($id!=0))
		{
			// On récupère le form
			$sql = "SELECT * FROM form WHERE ID = :num";
			$res = $db->prepare($sql);
			$res->bindValue(':num', $id, PDO::PARAM_INT);
			$r = $res->execute();
			$l = $res->fetch();

			if(!isset($l['NOM'])) {
				header('Location: index.php');
			}

			echo utf8_encode('<h1>'.$l['TITRE'].'</h1>');

			$form = new Zebra_Form($l['NOM']);
			$form->language("francais");

			$sql = "SELECT * FROM form_item WHERE ID_FORM = :num ORDER BY POSITION";
			$res = $db->prepare($sql);
			$res->bindValue(':num', $id, PDO::PARAM_INT);
			$r = $res->execute();
			$i = 0;
			while($l = $res->fetch()) {
				$items[$i] = $l;
				$i++;
			}

			/* 
			** On affiche tous les champs du formulaire avec les dépendances, etc...
			** CHAMPS PRIS EN COMPTE : date / email (texte) / select / radio
			*/
			foreach($items as $o) 
			{
				switch($o['TYPE']) {
					case 'date': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('date', $o['NOM']);

						$rules = array();
						$rules['date'] = array('error', 'Date invalide !');
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'email': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('text', $o['NOM']);

						$rules = array();
						$rules['email'] = array('error', 'Format de mail invalide!');
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'texte':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('text', $o['NOM']);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'select': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('select', $o['NOM']);
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj->add_options($option);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'radio': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj = $form->add('radios', $o['NOM'], $option);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'checkbox':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj = $form->add('checkboxes', $o['NOM'], $option);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'file':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('file', $o['NOM']);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						$rules['upload'] = array('file_upload', ZEBRA_FORM_UPLOAD_RANDOM_NAMES, 'error', 'File could not be uploaded!');
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'password':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('password', $o['NOM']);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'textarea':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('textarea', $o['NOM']);

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
					case 'time':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['TITRE']));
						$obj = $form->add('time', $o['NOM'], date('H:i'), array('format' => 'hm'));

						$rules = array();
						if($o['DEP_NOM']!='') {
							$rules['dependencies'] = array(array($o['DEP_NOM'] => $o['DEP_VALUE']), 'mycallback, label_'.$o['NOM'].'');
						}
						if($o['REQUIRED'] == "OUI") {
							$rules['required'] = array("error", "Champ requis !");
						}
						$obj->set_rule($rules);
					break;
				}

				if($o['NOTE']!='') 
				{
					$form->add('note', 'note_'.$o['NOM'].'', $o['NOM'], utf8_encode($o['NOTE']));
				}
			} 
			/*
			** FIN DU MEGA FOREACH
			**
			*/
			$obj = $form->add('submit', 'my_submit', 'Valider');
			// validate the form
			if ($form->validate()) {
			    echo "<h1> Merci </h1> <p> Votre participation a bien été enregistrée ! </p>";
			}
			echo $form->render();

			echo "<p> <a href='index.php'> Retour a l'accueil </a> </p>";

		}
		?>
    </body>
</html>