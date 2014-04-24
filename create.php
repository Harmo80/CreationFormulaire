<?php
 // todo vérifier qu'il y a au moins un champ ? vérifier si déjà fait ?
// 		 vérifier l'état des steps ne pas passer une étape trop vite en bidouillant,etc, voir si ok ?
// 		vérifier name unique ? utile ou pas ? dépend de comment on génère me mini-hash 




	/*********************************
	**********************************
	*** 
	*** create.php = Accueil du backend de création de formulaire : Bouton "Créer Formulaire"
	*** create.php?step=1 = Création du formulaire (nom et titre)
	*** create.php?step=2 = Interface de création, bouton "Add Champ" et tableau de récap' du form avec tous les champs déjà créés
	*** create.php?step=3 = Création d'un nouveau champ... redirection vers step=2 ensuite.
	*** create.php?step=4 = Terminé... Ajout final.
	***
	*** Tous le formulaire transitera de page en page par $_SESSION
	*** Pour éviter qu'un petit malin saute une étape en trafiquant l'url, vérifier en tout temps le contenu de $_SESSION
	***
	**********************************
	**********************************/

	/* Organisation de $_SESSION
	*
	*	$_SESSION -> Nom
	*			  -> Titre
	*			  -> Champs -> 1 -> nom, titre, type, ...
	*			  			-> 2 -> nom, titre, type, ...
	*			  			-> 3 -> nom, titre, type, ...
	*			   			-> 4 -> nom, titre, type, ...
	*					...
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

			body h2 {
				text-align: center;
			}

			body p {
				text-align: center;
			}

			body table {
				margin: auto;
				text-align: center;
				border-collapse: collapse;
			}

			body table th, body table td {
				padding-right: 5px;
				padding-left: 5px;
			}

			body table caption {
				font-weight: bold;
				margin-bottom: 10px;
				margin-top: 30px;
			}
		</style>
    </head>

    <body>
		<?php

		// On récupère le numéro d'étape ou pas
		if(!isset($_GET['step']))
			$step = 0;
		else 
			{
				$step = (int)$_GET['step']; 
			}

		switch($step) {
				case 0: // ACCUEIL DU BACKEND
					echo '<h1> CreaForm By Harmo </h1>';
					echo '<h2> Créez un formulaire </h2>';
					echo '<p> Bienvenue dans l\'outil de création de formulaire CreaForm ! Pour commencer, cliquez sur le lien ci-dessous.';

					echo '<p><a href="create.php?step=1"> Commencez ! </a></p>';
				break;
				case 1: //CREATION DU FORMULAIRE
					$form = new Zebra_Form("CreateForm");

					$form->add('label', 'label_nomForm', 'nomForm', 'Saisir le nom du formulaire : ');
					$obj = $form->add('text', 'nomForm');
					$obj->set_rule(array(
						 'required'=>array('error', 'Ce champ est requis !'),
					     'alphanumeric' => array('', 'error', 'Vous ne pouvez choisir que des lettres sans espaces !'
					     )));
					$form->add('note', 'note_nom', 'nomForm', 'Le nom ne sera pas visible. Seulement des lettres sans espaces.');

					$form->add('label', 'label_titreForm', 'titreForm', 'Saisir le titre du formulaire : ');
					$obj = $form->add('text', 'titreForm');
					$obj->set_rule(array('required'=>array('error', 'Ce champ est requis !')));
					$form->add('note', 'note_titre', 'titreForm', 'Le titre du formulaire qui sera visible.');

					$form->add('label', 'label_typeVal', 'typeVal', 'Choisir le type de validation : ');
					$obj = $form->add('select', 'typeVal');
					$obj->add_options(array(
						'valDatabase' => utf8_decode("Enregistrez les résultats en Base de données (recommandé)"),
					    'valMail' => utf8_decode("Envoyez les résultats par Mail")
					));
					$obj->set_rule(array('required'=>array('error', 'Ce champ est requis !')));
					$form->add('note', 'note_typeval', 'typeVal', utf8_decode('Par mail : chaque soumission de votre formulaire vous sera envoyé. <br /> En base de données : Vous vous connectez avec votre mail et votre mot de passe pour consulter les résultats.'));

					$form->add('label', 'label_Mail', 'Mail', 'Votre Email : ');
					$obj = $form->add('text', 'Mail');
					$obj->set_rule(array('required'=>array('error', 'Ce champ est requis !')));
					$form->add('note', 'note_mail', 'Mail', utf8_decode('Ce mail vous sera demandé pour vous connecter ensuite.'));

					$form->add('label', 'label_Mdp', 'Mdp', 'Votre Mot de Passe : ');
					$obj = $form->add('password', 'Mdp');
					$obj->set_rule(array('required'=>array('error', 'Ce champ est requis !')));
					$form->add('note', 'note_mdp', 'Mdp', utf8_decode('Ce mot de passe vous sera demandé pour vous connecter ensuite.'));

					$obj = $form->add('submit', 'my_submit', 'Poster');

					if ($form->validate()) {
						$_SESSION['Nom'] = $_POST['nomForm'];
						$_SESSION['Titre'] = $_POST['titreForm'];

						$_SESSION['TypeVal'] = $_POST['typeVal'];

						$_SESSION['Mail'] = $_POST['Mail'];
						$_SESSION['Mdp'] = $_POST['Mdp'];

						header('Location: create.php?step=2');
						}
					echo utf8_encode($form->render());
				break;
				case 2: // RECAP DES CHAMPS CREES DANS $_SESSION 
				if(isset($_SESSION['Nom']))
				{
					echo '<table border="1">
							<caption> Formulaire </caption>
							<tr>
								<th> Nom (privé) </th>
								<th> Titre (publique) </th>
							</tr>
						  	<tr>
						  		<td> '.$_SESSION['Nom'].' </td>
						  		<td> '.$_SESSION['Titre'].' </td>
						  	</tr></table>';

					echo '<p><a href="create.php?step=3"> Ajouter un champ </a></p>';

					echo '<table border="1">
						<caption> Champs du Formulaire </caption>
						<tr>
							<th> Nom </th>
							<th> Titre </th>
							<th> Type </th>
							<th> Notes </th>
							<th> Requis ? </th>
							<th> Dépendances </th>
							<th> Monter </th>
							<th> Descendre </th>
						</tr>';

					// Affichage des champs s'il y a
					if(isset($_SESSION['Champs'])) 
					{
						$i = 0;
						foreach($_SESSION['Champs'] as $c) 
						{
							echo '<tr>
								<td> '.$c['Nom'].' </td>
								<td> '.$c['Titre'].' </td>
								<td> '.$c['Type'].' </td>
								<td> '.$c['Note'].' </td>
								<td> '.$c['Requis'].' </td>
								<td> '.$c['dep'].' = '.$c['valuedep'].' </td>
								<td> <a href="backend?step=5&action=monter&id='.$i.'"> Monter </a> </td>
								<td> <a href="backend?step=5&action=descendre&id='.$i.'"> Descendre </a> </td>
							</tr>';
							$i++;
						}

						echo '<p><a href="create.php?step=4"> Terminer le formulaire </a></p>';
					}
					else
					{
						echo '<tr><td colspan="8"> Il n\'y aucun champ dans ce formulaire ! </td></tr>';
					}

					echo '</table>';
				}
				else header('Location: create.php?step=1');
				break;
				case 3: // CREATION DE CHAMP
				if(isset($_SESSION['Nom']))
				{
					$form = new Zebra_Form("CreateForm");
					$form->add('label', 'label_nomChamp', 'nomChamp', 'Saisir le nom du champ : ');
					$obj = $form->add('text', 'nomChamp');
					$obj->set_rule(array(
						 'required'=>array('error', 'Ce champ est requis !'),
					     'alphabet' => array(
					        '',
					        'error',
					        'Vous ne pouvez choisir que des lettres sans espaces !'
					     )));

					$form->add('label', 'label_titreChamp', 'titreChamp', 'Saisir le titre du champ : ');
					$obj = $form->add('text', 'titreChamp');
					$obj->set_rule(array(
						 'required'=>array('error', 'Ce champ est requis !')));

					$form->add('label', 'label_typeChamp', 'typeChamp', 'Choisir le type de champ : ');
					$obj = $form->add('select', 'typeChamp');
					$obj->add_options(array(
					    'date' => "Champ de date (Jour)",
					    'time' => "Champ de date (Heures, minutes)",
					    'texte' => "Champ de texte (une seule ligne)",
					    'textarea' => "Champ de texte (multiligne)",
					    'email' => "Champ d'email",
					    'password' => "Champ de mot de passe",
					    'select' => "Liste deroulante",
					    'radio' => "Cases a cocher (un seul choix)",
					    'checkbox' => "Cases a cocher (plusieurs choix)",
					    'file' => "Champ d'upload de fichier"
					));
					$obj->set_rule(array(
						 'required'=>array('error', 'Ce champ est requis !')));

					$form->add('label', 'label_optChamp', 'optChamp', 'Choisir les options pour le champ : ');
					$obj = $form->add('textarea', 'optChamp');
					$obj->set_rule(array(
					    'dependencies' => array(array(
					     'typeChamp' => array('select', 'radio', 'checkbox')
					     ), 'mycallback, label_optChamp') ));

					// Dépendances entre champs 
					if(isset($_SESSION['Champs'])) 
					{
						$form->add('label', 'label_booldepChamp', 'booldepChamp', "Ce champ est-il dépendant d'une réponse d'un autre champ (déjà créé)  ?");
						$obj = $form->add('checkbox', 'booldepChamp', 'Oui');

						$champcreated = array();
						foreach($_SESSION['Champs'] as $c)
						{
							$champcreated[$c['Nom']] = $c['Titre'];
						}

						$form->add('label', 'label_depChamp', 'depChamp', 'Choisir le champ lié : ');
						$obj = $form->add('select', 'depChamp');
						$obj->add_options($champcreated);
						$obj->set_rule(array(
						    'dependencies' => array(array(
						     'booldepChamp' => array('Oui')
						     ), 'mycallback, label_depChamp')));

						$form->add('label', 'label_valuedepChamp', 'valuedepChamp', 'Saisir la valeur pour laquelle la condition est valable : ');
						$obj = $form->add('text', 'valuedepChamp');
						$obj->set_rule(array(
						    'dependencies' => array(array(
						     'booldepChamp' => array('Oui')
						     ), 'mycallback, label_valuedepChamp')));

					}

					$form->add('label', 'label_noteChamp', 'noteChamp', 'Saisir un commentaire pour le champ (indication optionnelle) : ');
					$obj = $form->add('text', 'noteChamp');

					$form->add('label', 'label_requisChamp', 'requisChamp', 'Champ requis ? ');
					$obj = $form->add('checkbox', 'requisChamp', 'Oui');

					$obj = $form->add('submit', 'my_submit', 'Submit');

					if ($form->validate()) {
						// On ajoute tout ce qu'il faut dans SESSION
						if(isset($_SESSION['Champs'])) 
						{
							$nb = count($_SESSION['Champs']);
						}
						else
						{
							$nb = 0;
						}

						$_SESSION['Champs'][$nb]['Nom'] = $_POST['nomChamp'];
						$_SESSION['Champs'][$nb]['Titre'] = $_POST['titreChamp'];
						$_SESSION['Champs'][$nb]['Type'] = $_POST['typeChamp'];
						$_SESSION['Champs'][$nb]['Opt'] = $_POST['optChamp'];
						$_SESSION['Champs'][$nb]['Note'] = $_POST['noteChamp'];
						if(isset($_POST['requisChamp'])) 
						{
							$requis = 'OUI';
							$erreur = 'Ce champ est obligatoire !';
						}
						else 
						{
							$requis = 'NON';
							$erreur = '';
						}
						if(isset($_POST['booldepChamp']))
						{
							$_SESSION['Champs'][$nb]['dep'] = $_POST['depChamp'];
							$_SESSION['Champs'][$nb]['valuedep'] = $_POST['valuedepChamp'];
						}
						else
						{
							$_SESSION['Champs'][$nb]['dep'] = '';
							$_SESSION['Champs'][$nb]['valuedep'] = '';
						}
						$_SESSION['Champs'][$nb]['Requis'] = $requis;
						$_SESSION['Champs'][$nb]['Erreur'] = $erreur;

						header('Location: create.php?step=2');
						}
						echo utf8_encode($form->render());
					}
					else header('Location: create.php?step=1');
				break;
				case 4: // AJOUT FINAL DANS LA BDD
				if((isset($_SESSION['Nom']))&&(isset($_SESSION['Champs'])))
				{
					// Ajout du Form
					$sql = "INSERT INTO form(HASH, NOM, TITRE, INC_FORM_VALIDATE, INC_MAIL, INC_PASS) 
							VALUES(?, ?, ?, ?, ?)";
					$res = $db->prepare($sql);
					$r = $res->execute(array(sha1($_SESSION['Nom'].microtime()), $_SESSION['Nom'], $_SESSION['Titre'], $_SESSION['TypeVal'], $_SESSION['Mail'], $_SESSION['Mdp']));

					// Récupération de son ID 
					$sql = "SELECT * FROM form ORDER BY ID DESC LIMIT 1";
					$res = $db->prepare($sql);
					$r = $res->execute();
					$l = $res->fetch();

					// On ajoute les champs $i sert pour la position des champs 
					$i = 1;
					foreach($_SESSION['Champs'] as $c)
					{
						$sql = "INSERT INTO form_item(ID_FORM, NOM, TITRE, TYPE, NOTE, REQUIRED, ERROR, VALUE, DEP_NOM, DEP_VALUE, POSITION) 
								VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
						$res = $db->prepare($sql);
						$r = $res->execute(array($l['ID'], $c['Nom'], $c['Titre'], $c['Type'], $c['Note'], $c['Requis'], $c['Erreur'], $c['Opt'], $c['dep'], $c['valuedep'], $i));						
						$i++;
					}
				}
				else header('Location: create.php?step=1');
				break;
				case 5: // FAIRE MONTER OU DESCENDRE DES CHAMPS
				// ici on récupère $_GET step, action et id
				if((isset($_SESSION['Nom']))&&(isset($_SESSION['Champs']))) // on vérifie que le minimum existe
				{
					$id = $_GET['id'];
					if(isset($_SESSION['Champs'][$id])) // On vérifie que le champ à bouger existe
					{
						if($_GET['action']=='monter') // on vérifie qu'il n'est pas déjà tout en haut ...
						{
							if(isset($_SESSION['Champs'][$id-1]))
							{ // ICI ON ECHANGE LES PLACES
								$swap = $_SESSION['Champs'][$id-1]; 
								$_SESSION['Champs'][$id-1] = $_SESSION['Champs'][$id]; 
								$_SESSION['Champs'][$id] = $swap;
								header('Location: create.php?step=2');
							}
							else header('Location: create.php?step=2');
						}
						elseif($_GET['action']=='descendre') // On vérifie qu'il n'est pas déjà tout en bas (si un champ existe plus bas)
						{
							if(isset($_SESSION['Champs'][$id+1]))
							{ // ICI ON ECHANGE LES PLACES
								$swap = $_SESSION['Champs'][$id+1]; 
								$_SESSION['Champs'][$id+1] = $_SESSION['Champs'][$id]; 
								$_SESSION['Champs'][$id] = $swap;
								
								header('Location: create.php?step=2');
							}
							else header('Location: create.php?step=2');
						}
						else // l'action n'existe pas
						{
							header('Location: create.php?step=2');
						}

					}

				}

				break;
			}

		echo '<p> <a href="index.php"> Quitter </a> <br />(Attention, cela détruira votre formulaire en cours !)</p>';
		?>
    </body>
</html>