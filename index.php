<?php
	/* 
	* Page index.php
	* Page d'accueil du site CreaForm
	* Présentation du site
	* Possibilité d'accéder à un formulaire en passant en GET id=X
	* X étant l'id du formulaire demandé
	*/

	/* Inclusion de la librairie ZebraForm */
	require 'zebra_form.2.9.1/Zebra_Form.php';

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
        <link rel="stylesheet" href="zebra_form.2.9.1/public/css/zebra_form.css">
        <script src="zebra_form.2.9.1/public/javascript/jquery.js"></script>
        <script src="zebra_form.2.9.1/public/javascript/zebra_form.js"></script>
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

			$form = new Zebra_Form($l['NOM']);

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
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('date', $o['NOM']);
						$obj->set_rule(
							array(
					     	'date' => array(
						        'error',        // variable to add the error message to
						        'Invalid date!' // error message if value doesn't validate
					     		),
					     	'dependencies' => array(
					     		array(
					     			$o['DEP_NOM'] => $o['DEP_VALUE']
					     			), 'mycallback, label_'.$o['NOM'].''
					     		) 
					     	)
					     );
					break;
					case 'email': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('text', $o['NOM']);
						$obj->set_rule(array(
							'email' => array(
					        'error',                    // variable to add the error message to
					        'Format de mail invalide!'    // error message if value doesn't validate
					     	),
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'texte':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('text', $o['NOM']);
						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'select': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('select', $o['NOM']);
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj->add_options($option);
						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'radio': 
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj = $form->add('radios', $o['NOM'], $option);

						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'checkbox':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$options = explode("\n", $o['VALUE']);

						foreach($options as $opt) {
							$opt = preg_replace('`[^0-9A-Za-z ]`', '', $opt);
							$option[$opt] = $opt;
						}

						$obj = $form->add('checkboxes', $o['NOM'], $option);

						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'file':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('file', $o['NOM']);
						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].''),
					     'upload' => array(
					        'tmp',                              // path to upload file to
					        ZEBRA_FORM_UPLOAD_RANDOM_NAMES,     // upload file with random-generated name
					        'error',                            // variable to add the error message to
					        'File could not be uploaded!'       // error message if value doesn't validate
					     )
      					));
					break;
					case 'password':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('password', $o['NOM']);
						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'textarea':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('textarea', $o['NOM']);
						$obj->set_rule(array(
					     'dependencies' => array(array(
					     	$o['DEP_NOM'] => $o['DEP_VALUE']
					     	), 'mycallback, label_'.$o['NOM'].'') ));
					break;
					case 'time':
						$form->add('label', 'label_'.$o['NOM'].'', $o['NOM'], $o['TITRE']);
						$obj = $form->add('time', $o['NOM'], date('H:i'), array('format' => 'hm'));
						$obj->set_rule(array(
					     	'dependencies' => array(
					     		array(
					     			$o['DEP_NOM'] => $o['DEP_VALUE']
					     			), 'mycallback, label_'.$o['NOM'].''
					     		) 
					     	)
					     );
					break;
				}

				if($o['REQUIRED']=='OUI')
				{
					$obj->set_rule(array('required'=>array('error', $o['ERROR'])));
				}

				if($o['NOTE']!='') 
				{
					$form->add('note', 'note_'.$o['NOM'].'', $o['NOM'], $o['NOTE']);
				}
			} 
			/*
			** FIN DU MEGA FOREACH
			**
			*/
			$obj = $form->add('submit', 'my_submit', 'Submit');
			// validate the form
			if ($form->validate()) {
			    echo "SUBMITTED ! ";
			}
			// auto generate output, labels above form elements
			$form->render();

			echo "<p> <a href='frontend.php'> Retour a l'accueil </a> </p>";

		}
		?>
    </body>
</html>